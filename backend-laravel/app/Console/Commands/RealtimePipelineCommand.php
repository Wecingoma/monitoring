<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\Alert;
use App\Services\ZabbixService;
use App\Services\AiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RealtimePipelineCommand extends Command
{
    protected $signature = 'monitoring:realtime {--interval=30 : Sync interval in seconds}';
    protected $description = 'Realtime pipeline: Zabbix sync → AI detection → Alerts';

    private const METRIC_THRESHOLDS = [
        'cpu_usage' => ['critical' => 90, 'warning' => 75],
        'ram_usage' => ['critical' => 90, 'warning' => 80],
        'disk_usage' => ['critical' => 95, 'warning' => 85],
    ];

    private const KEY_MAP = [
        'vm.memory.util' => 'ram',
        'system.cpu.util' => 'cpu',
        'vfs.fs.dependent.size' => 'disk',
        'vfs.fs.size' => 'disk',
        'net.if.in' => 'network',
        'net.if.out' => 'network',
    ];

    private const UNIT_MAP = [
        'cpu' => '%',
        'ram' => '%',
        'disk' => '%',
        'network' => 'Mbps',
    ];

    public function handle(): int
    {
        $interval = (int) $this->option('interval');
        $this->info("=== MonitorIA Realtime Pipeline (interval: {$interval}s) ===");
        $this->info('Press Ctrl+C to stop');
        $this->newLine();

        $cycle = 0;

        while (true) {
            $cycle++;
            $start = microtime(true);
            $this->info('[' . date('H:i:s') . "] === Cycle #{$cycle} ===");

            try {
                $this->syncZabbix();
                $this->runAiDetection();
                $this->checkThresholds();
                Cache::flush();
            } catch (\Exception $e) {
                $this->error('Pipeline error: ' . $e->getMessage());
                Log::error('Realtime pipeline error', ['message' => $e->getMessage()]);
            }

            $elapsed = round(microtime(true) - $start, 2);
            $sleep = max(1, $interval - (int) $elapsed);
            $this->line("  Cycle completed in {$elapsed}s — next sync in {$sleep}s");
            $this->newLine();
            sleep($sleep);
        }

        return self::SUCCESS;
    }

    private function syncZabbix(): void
    {
        $zabbix = app(ZabbixService::class);

        $hosts = $zabbix->getHosts([
            'output' => ['hostid', 'host', 'name', 'status', 'description'],
            'selectInterfaces' => ['interfaceid', 'ip', 'available', 'type', 'dns'],
            'filter' => ['status' => 0],
        ]);

        if (empty($hosts)) {
            $hosts = $zabbix->getHosts([
                'output' => ['hostid', 'host', 'name', 'status', 'description'],
                'selectInterfaces' => ['interfaceid', 'ip', 'available', 'type', 'dns'],
            ]);
        }

        if (empty($hosts)) {
            $this->warn('  No hosts found');
            return;
        }

        $serverCount = 0;
        $metricCount = 0;

        foreach ($hosts as $zbx) {
            if (($zbx['status'] ?? '0') !== '0') continue;

            $ip = $zbx['interfaces'][0]['ip'] ?? $zbx['interfaces'][0]['dns'] ?? '0.0.0.0';
            $avail = $zbx['interfaces'][0]['available'] ?? '0';
            $status = match ($avail) {
                '1' => 'online',
                '2' => 'offline',
                default => 'warning',
            };

            $server = Server::updateOrCreate(
                ['zabbix_host_id' => $zbx['hostid']],
                [
                    'name' => $zbx['name'] ?? $zbx['host'],
                    'hostname' => $zbx['host'],
                    'ip_address' => $ip,
                    'status' => $status,
                    'description' => $zbx['description'] ?? null,
                    'last_check_at' => now(),
                    'user_id' => 1,
                ]
            );
            $serverCount++;

            $metrics = $zabbix->getMetrics($zbx['hostid']);
            if (empty($metrics)) continue;

            $latestValues = [];
            $diskValues = [];
            $netValues = 0;

            foreach ($metrics as $key => $data) {
                $type = null;
                foreach (self::KEY_MAP as $zbxKey => $typeKey) {
                    if (str_starts_with($key, $zbxKey)) {
                        $type = $typeKey;
                        break;
                    }
                }
                if (!$type) continue;

                $units = $data['units'] ?? '';

                if ($type === 'disk' && !str_contains($key, 'pused')) continue;
                if ($type === 'network' && (str_contains($key, 'errors') || str_contains($key, 'dropped') || str_contains($key, '.type') || str_contains($key, '.speed') || str_contains($key, '.status'))) continue;
                if ($type === 'ram' && !str_contains($key, 'util')) continue;

                $lastValue = $data['lastvalue'] ?? null;
                if ($lastValue !== null && is_numeric($lastValue)) {
                    $val = (float) $lastValue;

                    if ($type === 'network') {
                        $val = $val / 1000000;
                        $netValues += $val;
                    } elseif ($type === 'disk') {
                        $diskValues[] = min(100, max(0, round($val, 2)));
                    } else {
                        $val = min(100, max(0, round($val, 2)));
                        $latestValues[$type] = $val;
                    }

                    \App\Models\Metric::updateOrCreate(
                        [
                            'server_id' => $server->id,
                            'type' => $type,
                            'recorded_at' => now()->setSeconds(0)->setMicroseconds(0),
                        ],
                        [
                            'value' => $type === 'network' ? round($netValues, 4) : ($type === 'disk' ? end($diskValues) : $val),
                            'unit' => self::UNIT_MAP[$type] ?? $units,
                        ]
                    );
                    $metricCount++;
                }
            }

            if (!empty($diskValues)) {
                $latestValues['disk'] = round(array_sum($diskValues) / count($diskValues), 2);
            }
            if ($netValues > 0) {
                $latestValues['network'] = round($netValues, 4);
            }

            if (!empty($latestValues)) {
                if (isset($latestValues['cpu'])) $server->cpu_usage = $latestValues['cpu'];
                if (isset($latestValues['ram'])) $server->ram_usage = $latestValues['ram'];
                if (isset($latestValues['disk'])) $server->disk_usage = $latestValues['disk'];
                if (isset($latestValues['network'])) $server->network_usage = $latestValues['network'];
                $server->last_check_at = now();
                $server->save();
            }
        }

        $problems = $zabbix->getProblems();
        foreach ($problems as $problem) {
            $hostName = $problem['hosts'][0]['name'] ?? 'Unknown';
            $severity = match ((string) ($problem['severity'] ?? '0')) {
                '5', '4' => 'critical',
                '3', '2' => 'warning',
                default => 'info',
            };
            Alert::firstOrCreate(
                [
                    'source' => 'zabbix',
                    'title' => $problem['name'] ?? 'Zabbix Problem',
                    'created_at' => date('Y-m-d H:i:s', $problem['clock']),
                ],
                [
                    'description' => "Probleme Zabbix sur {$hostName}: " . ($problem['name'] ?? ''),
                    'severity' => $severity,
                    'status' => 'active',
                    'server_id' => Server::where('hostname', $problem['hosts'][0]['host'] ?? '')->value('id'),
                    'user_id' => 1,
                ]
            );
        }

        $this->info("  Zabbix: {$serverCount} servers, {$metricCount} metrics, " . count($problems) . " problems");
    }

    private function runAiDetection(): void
    {
        $ai = app(AiService::class);

        try {
            $health = $ai->getHealthStatus();
            if (($health['status'] ?? '') !== 'healthy') {
                $this->warn('  AI: Service unavailable — skipping detection');
                return;
            }
        } catch (\Exception $e) {
            $this->warn('  AI: Cannot connect — skipping detection');
            return;
        }

        $servers = Server::whereIn('status', ['online', 'warning'])->get();
        $types = ['cpu', 'ram', 'disk', 'network'];
        $anomalyCount = 0;

        foreach ($servers as $server) {
            foreach ($types as $type) {
                $hasMetrics = $server->metrics()->where('type', $type)->exists();
                if (!$hasMetrics) continue;

                $result = $ai->detectAnomaly($server, $type);

                if ($result['anomaly'] ?? false) {
                    $anomalyCount++;
                }
            }
        }

        $this->info("  AI: Scanned {$servers->count()} servers, found {$anomalyCount} anomalies");
    }

    private function checkThresholds(): void
    {
        $servers = Server::all();
        $alertCount = 0;

        foreach ($servers as $server) {
            foreach (self::METRIC_THRESHOLDS as $field => $thresholds) {
                $value = (float) ($server->{$field} ?? 0);
                if ($value <= 0) continue;

                $severity = null;
                if ($value >= $thresholds['critical']) {
                    $severity = 'critical';
                } elseif ($value >= $thresholds['warning']) {
                    $severity = 'warning';
                }

                if (!$severity) continue;

                $metricLabel = match ($field) {
                    'cpu_usage' => 'CPU',
                    'ram_usage' => 'RAM',
                    'disk_usage' => 'Disque',
                    default => $field,
                };

                $existing = Alert::where('server_id', $server->id)
                    ->where('source', 'ia')
                    ->where('severity', $severity)
                    ->where('title', 'like', "%{$metricLabel}%")
                    ->where('status', 'active')
                    ->exists();

                if (!$existing) {
                    Alert::create([
                        'title' => "{$metricLabel} {$server->name} à {$value}%",
                        'description' => "Le serveur {$server->name} a une utilisation {$metricLabel} de {$value}%, dépassant le seuil {$severity} de " . $thresholds[$severity] . "%",
                        'severity' => $severity,
                        'status' => 'active',
                        'source' => 'ia',
                        'server_id' => $server->id,
                        'user_id' => 1,
                    ]);
                    $alertCount++;
                }
            }
        }

        if ($alertCount > 0) {
            $this->info("  Thresholds: {$alertCount} new alerts generated");
        }
    }
}
