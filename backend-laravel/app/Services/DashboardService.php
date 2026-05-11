<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Alert;
use App\Models\Anomaly;
use App\Models\SystemLog;
use App\Models\Metric;
use App\Models\Incident;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getOverviewStats(): array
    {
        return Cache::remember('dashboard_overview', 10, function () {
            return [
                'total_servers' => Server::count(),
                'online_servers' => Server::online()->count(),
                'offline_servers' => Server::offline()->count(),
                'warning_servers' => Server::warning()->count(),
                'critical_alerts' => Alert::active()->critical()->count(),
                'warning_alerts' => Alert::active()->warning()->count(),
                'total_anomalies' => Anomaly::notFalsePositive()->recent()->count(),
                'critical_anomalies' => Anomaly::notFalsePositive()->critical()->recent()->count(),
                'open_incidents' => Incident::open()->count(),
            ];
        });
    }

    public function getSystemMetrics(): array
    {
        return Cache::remember('dashboard_metrics', 10, function () {
            $servers = Server::whereIn('status', ['online', 'warning'])->get();
            
            return [
                'avg_cpu' => $servers->avg('cpu_usage'),
                'avg_ram' => $servers->avg('ram_usage'),
                'avg_disk' => $servers->avg('disk_usage'),
                'avg_network' => $servers->avg('network_usage'),
                'servers' => $servers->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'hostname' => $s->hostname,
                    'status' => $s->status,
                    'cpu' => $s->cpu_usage,
                    'ram' => $s->ram_usage,
                    'disk' => $s->disk_usage,
                    'network' => $s->network_usage,
                    'uptime' => $s->uptime,
                ]),
            ];
        });
    }

    public function getRecentAlerts(int $limit = 10): array
    {
        return Cache::remember('dashboard_recent_alerts', 10, function () use ($limit) {
            return Alert::with('server')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'severity' => $a->severity,
                    'status' => $a->status,
                    'source' => $a->source,
                    'server_name' => $a->server?->name,
                    'created_at' => $a->created_at->diffForHumans(),
                ])
                ->toArray();
        });
    }

    public function getRecentAnomalies(int $limit = 10): array
    {
        return Cache::remember('dashboard_recent_anomalies', 10, function () use ($limit) {
            return Anomaly::with('server')
                ->notFalsePositive()
                ->orderBy('detected_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'score' => $a->score,
                    'severity' => $a->severity,
                    'description' => $a->description,
                    'server_name' => $a->server?->name,
                    'detected_at' => $a->detected_at->diffForHumans(),
                ])
                ->toArray();
        });
    }

    public function getRecentLogs(int $limit = 20): array
    {
        return Cache::remember('dashboard_recent_logs', 10, function () use ($limit) {
            return SystemLog::with('server')
                ->orderBy('logged_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($l) => [
                    'id' => $l->id,
                    'level' => $l->level,
                    'message' => $l->message,
                    'source' => $l->source,
                    'server_name' => $l->server?->name,
                    'logged_at' => $l->logged_at->diffForHumans(),
                ])
                ->toArray();
        });
    }

    public function getAlertsBySeverityChart(): array
    {
        return Cache::remember('dashboard_alerts_chart', 10, function () {
            $alerts = Alert::selectRaw('severity, count(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray();

            return [
                'critical' => $alerts['critical'] ?? 0,
                'warning' => $alerts['warning'] ?? 0,
                'info' => $alerts['info'] ?? 0,
            ];
        });
    }

    public function getMetricsTrend(string $type = 'cpu', int $hours = 24): array
    {
        return Cache::remember("dashboard_metrics_trend_{$type}", 10, function () use ($type, $hours) {
            return Metric::where('type', $type)
                ->where('recorded_at', '>=', now()->subHours($hours))
                ->selectRaw("TO_CHAR(recorded_at, 'YYYY-MM-DD HH24:00') as hour, AVG(value) as avg_value")
                ->groupByRaw("TO_CHAR(recorded_at, 'YYYY-MM-DD HH24:00')")
                ->orderBy('hour')
                ->pluck('avg_value', 'hour')
                ->toArray();
        });
    }

    public function getFullDashboard(): array
    {
        return [
            'stats' => $this->getOverviewStats(),
            'metrics' => $this->getSystemMetrics(),
            'recent_alerts' => $this->getRecentAlerts(),
            'recent_anomalies' => $this->getRecentAnomalies(),
            'recent_logs' => $this->getRecentLogs(),
            'alerts_chart' => $this->getAlertsBySeverityChart(),
            'cpu_trend' => $this->getMetricsTrend('cpu'),
            'ram_trend' => $this->getMetricsTrend('ram'),
            'disk_trend' => $this->getMetricsTrend('disk'),
            'network_trend' => $this->getMetricsTrend('network'),
        ];
    }
}