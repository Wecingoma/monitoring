<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\Metric;
use App\Models\Alert;
use App\Services\ZabbixService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncZabbixCommand extends Command
{
    protected $signature = 'sync:zabbix {--force : Force sync even if recently synced}';
    protected $description = 'Synchronize hosts, metrics and problems from Zabbix';

    private const KEY_MAP = [
        'vm.memory.util' => 'ram',
        'system.cpu.util' => 'cpu',
        'system.cpu.load' => 'cpu',
        'vfs.fs.dependent.size' => 'disk',
        'vfs.fs.size' => 'disk',
        'net.if.in' => 'network',
        'net.if.out' => 'network',
        'net.if.total' => 'network',
    ];

    private const UNIT_MAP = [
        'cpu' => '%',
        'ram' => '%',
        'disk' => '%',
        'network' => 'Mbps',
    ];

    private const FIELD_MAP = [
        'cpu' => 'cpu_usage',
        'ram' => 'ram_usage',
        'disk' => 'disk_usage',
        'network' => 'network_usage',
    ];

    private function normalizeValue(float $value, string $units, string $type, string $key = ''): float
    {
        if ($type === 'cpu') {
            return min(100, max(0, round($value, 2)));
        }

        if ($type === 'ram') {
            if ($units === 'B' || $units === 'bytes') {
                return min(100, max(0, round($value, 2)));
            }
            return min(100, max(0, round($value, 2)));
        }

        if ($type === 'disk') {
            if (str_contains($key, 'pused') || $units === '%') {
                return min(100, max(0, round($value, 2)));
            }
            if ($units === 'B' || $units === 'bytes') {
                return min(100, max(0, round($value, 2)));
            }
            return min(100, max(0, round($value, 2)));
        }

        if ($type === 'network') {
            if ($units === 'bps') {
                return min(99999999, max(0, round($value / 1000000, 4)));
            }
            return min(99999999, max(0, round($value, 2)));
        }

        return min(99999999, max(0, round($value, 2)));
    }

    public function handle(): int
    {
        $zabbix = app(ZabbixService::class);

        $this->info('Connecting to Zabbix...');

        $hosts = $zabbix->getHosts([
            'output' => ['hostid', 'host', 'name', 'status', 'description'],
            'selectInterfaces' => ['interfaceid', 'ip', 'available', 'type', 'dns'],
            'filter' => ['status' => 0],
        ]);

        if (empty($hosts)) {
            $this->warn('No monitored hosts found. Trying without filter...');
            $hosts = $zabbix->getHosts([
                'output' => ['hostid', 'host', 'name', 'status', 'description'],
                'selectInterfaces' => ['interfaceid', 'ip', 'available', 'type', 'dns'],
            ]);
        }

        if (empty($hosts)) {
            $this->error('No hosts found in Zabbix.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d hosts', count($hosts)));

        $synced = 0;
        $updated = 0;

        foreach ($hosts as $zbx) {
            if (($zbx['status'] ?? '0') !== '0') {
                continue;
            }

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

            if ($server->wasRecentlyCreated) {
                $synced++;
            } else {
                $updated++;
            }

            $this->syncHostMetrics($zabbix, $server, $zbx['hostid']);
        }

        $this->info("Servers: {$synced} created, {$updated} updated");
        $this->syncProblems($zabbix);

        return self::SUCCESS;
    }

    private function syncHostMetrics(ZabbixService $zabbix, Server $server, string $hostId): void
    {
        $this->line("  Syncing metrics for {$server->name} (hostid={$hostId})...");

        $metrics = $zabbix->getMetrics($hostId);

        if (empty($metrics)) {
            $this->warn("  No metrics found for {$server->name}");
            return;
        }

        $this->line("  Found " . count($metrics) . " items");

        $latestValues = [];
        $matchedTypes = [];

        foreach ($metrics as $key => $data) {
            $type = null;
            foreach (self::KEY_MAP as $zbxKey => $typeKey) {
                if (str_starts_with($key, $zbxKey)) {
                    $type = $typeKey;
                    break;
                }
            }

            if (!$type) {
                continue;
            }

            $units = $data['units'] ?? '';

            if ($type === 'disk' && !str_contains($key, 'pused')) {
                continue;
            }

            if ($type === 'network' && (str_contains($key, 'errors') || str_contains($key, 'dropped') || str_contains($key, '.type') || str_contains($key, '.speed') || str_contains($key, '.status'))) {
                continue;
            }

            if ($type === 'ram' && !str_contains($key, 'util')) {
                continue;
            }

            $lastValue = $data['lastvalue'] ?? null;
            if ($lastValue !== null && is_numeric($lastValue)) {
                $normalizedLastValue = $this->normalizeValue((float) $lastValue, $units, $type, $key);

                if ($type === 'disk') {
                    $latestValues['disk_values'][] = $normalizedLastValue;
                } elseif ($type === 'network') {
                    if (!isset($latestValues['net_values'])) {
                        $latestValues['net_values'] = 0;
                    }
                    $latestValues['net_values'] += $normalizedLastValue;
                } else {
                    $latestValues[$type] = $normalizedLastValue;
                }
            }

            $history = $zabbix->getHistory($data['itemid'], 24, 48, (int) ($data['value_type'] ?? 0));

            foreach ($history as $point) {
                $rawValue = (float) ($point['value'] ?? 0);
                $normalizedValue = $this->normalizeValue($rawValue, $units, $type, $key);

                Metric::updateOrCreate(
                    [
                        'server_id' => $server->id,
                        'type' => $type,
                        'recorded_at' => date('Y-m-d H:i:s', (int) $point['clock']),
                    ],
                    [
                        'value' => $normalizedValue,
                        'unit' => self::UNIT_MAP[$type] ?? $units ?? '%',
                    ]
                );
            }
        }

        if (!empty($latestValues['disk_values'])) {
            $diskVals = $latestValues['disk_values'];
            $latestValues['disk'] = round(array_sum($diskVals) / count($diskVals), 2);
        }
        if (isset($latestValues['net_values'])) {
            $latestValues['network'] = round($latestValues['net_values'], 4);
        }

        if (!empty($latestValues)) {
            if (isset($latestValues['cpu'])) {
                $server->cpu_usage = $latestValues['cpu'];
            }
            if (isset($latestValues['ram'])) {
                $server->ram_usage = $latestValues['ram'];
            }
            if (isset($latestValues['disk'])) {
                $server->disk_usage = $latestValues['disk'];
            }
            if (isset($latestValues['network'])) {
                $server->network_usage = $latestValues['network'];
            }
            $server->last_check_at = now();
            $server->save();
        }

        $displayValues = array_filter($latestValues, fn($v, $k) => !in_array($k, ['disk_values', 'net_values']), ARRAY_FILTER_USE_BOTH);
        $fieldStr = implode(', ', array_map(
            fn($t) => "$t=" . ($displayValues[$t] ?? 'N/A'),
            array_keys($displayValues)
        ));
        $this->info("  Updated: $fieldStr");
    }

    private function syncProblems(ZabbixService $zabbix): void
    {
        $this->info('Syncing Zabbix problems...');
        $problems = $zabbix->getProblems();
        $alertCount = 0;

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
            $alertCount++;
        }

        $this->info("Alerts synced: {$alertCount}");
    }
}