<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metric;
use App\Models\Server;
use App\Services\ZabbixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    public function __construct(
        private ZabbixService $zabbixService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Metric::with('server');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $metrics = $query->orderBy('recorded_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($metrics);
    }

    public function serverMetrics(Server $server, Request $request): JsonResponse
    {
        $query = Metric::where('server_id', $server->id);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('hours')) {
            $query->recent($request->hours);
        }

        $metrics = $query->orderBy('recorded_at', 'desc')
            ->paginate($request->per_page ?? 100);

        return response()->json($metrics);
    }

    public function syncFromZabbix(Server $server): JsonResponse
    {
        $zabbixHostId = $server->zabbix_host_id;

        if (!$zabbixHostId) {
            return response()->json(['error' => 'Serveur non lié à Zabbix'], 400);
        }

        $metrics = $this->zabbixService->getMetrics($zabbixHostId);

        foreach ($metrics as $key => $metricData) {
            $type = $this->mapZabbixKeyToType($key);
            if ($type) {
                Metric::create([
                    'server_id' => $server->id,
                    'type' => $type,
                    'value' => $metricData['lastvalue'] ?? 0,
                    'unit' => $metricData['units'] ?? '',
                    'recorded_at' => now(),
                ]);
            }
        }

        return response()->json(['synced' => count($metrics)]);
    }

    private function mapZabbixKeyToType(string $key): ?string
    {
        return match (true) {
            str_contains($key, 'cpu') => 'cpu',
            str_contains($key, 'vmware') || str_contains($key, 'mem') => 'ram',
            str_contains($key, 'vfs') => 'disk',
            str_contains($key, 'net') => 'network',
            str_contains($key, 'system') => 'uptime',
            default => null,
        };
    }
}