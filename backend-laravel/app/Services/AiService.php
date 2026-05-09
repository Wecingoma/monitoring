<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Server;
use App\Models\Anomaly;

class AiService
{
    private string $serviceUrl;
    private int $timeout;

    public function __construct()
    {
        $this->serviceUrl = config('monitoring.ai.service_url', 'http://localhost:5000');
        $this->timeout = config('monitoring.ai.timeout', 30);
    }

    public function detectAnomaly(Server $server, string $metricType = 'cpu'): array
    {
        try {
            $metrics = $server->metrics()
                ->where('type', $metricType)
                ->orderBy('recorded_at', 'desc')
                ->limit(100)
                ->get()
                ->map(fn($m) => [
                    'value' => (float) $m->value,
                    'timestamp' => $m->recorded_at->timestamp,
                ])
                ->toArray();

            $response = Http::timeout($this->timeout)->post("{$this->serviceUrl}/api/anomaly/detect", [
                'server_id' => $server->id,
                'server_name' => $server->name,
                'metric_type' => $metricType,
                'data' => $metrics,
            ]);

            if ($response->successful()) {
                $result = $response->json();

                if ($result['anomaly'] ?? false) {
                    $this->storeAnomaly($server, $metricType, $result);
                }

                return $result;
            }

            Log::error('AI service error', ['status' => $response->status(), 'body' => $response->body()]);
            return ['anomaly' => false, 'score' => 0, 'error' => 'AI service unavailable'];
        } catch (\Exception $e) {
            Log::error('AiService detectAnomaly error', ['message' => $e->getMessage()]);
            return ['anomaly' => false, 'score' => 0, 'error' => $e->getMessage()];
        }
    }

    public function detectAllAnomalies(): array
    {
        try {
            $response = Http::timeout($this->timeout)->post("{$this->serviceUrl}/api/anomaly/detect-all");

            if ($response->successful()) {
                return $response->json();
            }

            return ['anomalies' => [], 'error' => 'AI service unavailable'];
        } catch (\Exception $e) {
            Log::error('AiService detectAllAnomalies error', ['message' => $e->getMessage()]);
            return ['anomalies' => [], 'error' => $e->getMessage()];
        }
    }

    public function getAnomalyStats(): array
    {
        try {
            $response = Http::timeout($this->timeout)->get("{$this->serviceUrl}/api/anomaly/stats");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('AiService getAnomalyStats error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getHealthStatus(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/api/health");

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'unhealthy', 'error' => 'Service unavailable'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private function storeAnomaly(Server $server, string $type, array $result): Anomaly
    {
        return Anomaly::create([
            'server_id' => $server->id,
            'type' => $type,
            'score' => $result['score'] ?? 0,
            'severity' => $result['severity'] ?? 'low',
            'description' => $result['description'] ?? 'Anomalie détectée par IA',
            'recommendation' => $result['recommendation'] ?? null,
            'data_points' => $result['data_points'] ?? null,
            'model_info' => $result['model_info'] ?? null,
            'detected_at' => now(),
        ]);
    }
}