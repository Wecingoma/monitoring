<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServerRequest;
use App\Models\Server;
use App\Services\AuditService;
use App\Services\ZabbixService;
use App\Services\AiService;
use App\Models\Metric;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private ZabbixService $zabbixService,
        private AiService $aiService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Server::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hostname', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $servers = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($servers);
    }

    public function store(ServerRequest $request): JsonResponse
    {
        $server = Server::create($request->validated());
        $this->auditService->logModel('server_created', $server);

        return response()->json($server->load('user'), 201);
    }

    public function show(Server $server): JsonResponse
    {
        return response()->json($server->load('user', 'metrics', 'alerts', 'anomalies'));
    }

    public function update(ServerRequest $request, Server $server): JsonResponse
    {
        $oldValues = $server->toArray();
        $server->update($request->validated());
        $this->auditService->logModel('server_updated', $server, null, $oldValues, $server->toArray());

        return response()->json($server->fresh()->load('user'));
    }

    public function destroy(Server $server): JsonResponse
    {
        $this->auditService->logModel('server_deleted', $server);
        $server->delete();

        return response()->json(null, 204);
    }

    public function syncFromZabbix(): JsonResponse
    {
        $hosts = $this->zabbixService->getHosts([
            'output' => ['hostid', 'host', 'name', 'status', 'description'],
            'selectInterfaces' => ['interfaceid', 'ip', 'available', 'type', 'dns'],
            'selectGroups' => ['groupid', 'name'],
            'filter' => ['status' => 0],
        ]);

        if (empty($hosts)) {
            return response()->json(['synced' => 0, 'total' => 0, 'message' => 'Aucun hôte supervisé trouvé dans Zabbix']);
        }

        $synced = 0;
        $updated = 0;
        foreach ($hosts as $zbx) {
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

            $this->syncHostMetrics($server, $zbx['hostid']);
        }

        $problems = $this->zabbixService->getProblems();
        $alertCount = 0;
        foreach ($problems as $problem) {
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
                    'description' => ($problem['name'] ?? '') . ' - ' . ($problem['hosts'][0]['name'] ?? ''),
                    'severity' => $severity,
                    'status' => 'active',
                    'server_id' => Server::where('hostname', $problem['hosts'][0]['host'] ?? '')->value('id'),
                    'user_id' => 1,
                ]
            );
            $alertCount++;
        }

        $this->auditService->log('zabbix_sync', "Synchronisé {$synced} nouveaux + {$updated} mis à jour, {$alertCount} alertes depuis Zabbix");

        return response()->json([
            'synced' => $synced + $updated,
            'created' => $synced,
            'updated' => $updated,
            'total' => count($hosts),
            'alerts' => $alertCount,
        ]);
    }

    private function syncHostMetrics(Server $server, string $hostId): void
    {
        $metrics = $this->zabbixService->getMetrics($hostId);

        $keyMap = [
            'system.cpu.util' => 'cpu',
            'vm.memory.util' => 'ram',
            'vfs.fs.size' => 'disk',
            'net.if.in' => 'network',
            'system.cpu.load' => 'cpu',
        ];

        foreach ($metrics as $key => $data) {
            $type = null;
            foreach ($keyMap as $zbxKey => $typeKey) {
                if (str_starts_with($key, $zbxKey)) {
                    $type = $typeKey;
                    break;
                }
            }

            if (!$type) {
                continue;
            }

            if ($data['lastvalue'] !== null) {
                $fieldMap = [
                    'cpu' => 'cpu_usage',
                    'ram' => 'ram_usage',
                    'disk' => 'disk_usage',
                    'network' => 'network_usage',
                ];

                if (isset($fieldMap[$type])) {
                    $server->{$fieldMap[$type]} = (float) $data['lastvalue'];
                }
            }

            if (!empty($data['history'])) {
                foreach (array_slice($data['history'], 0, 24) as $point) {
                    Metric::updateOrCreate(
                        [
                            'server_id' => $server->id,
                            'type' => $type,
                            'recorded_at' => date('Y-m-d H:i:s', $point['timestamp']),
                        ],
                        [
                            'value' => (float) $point['value'],
                            'unit' => $data['units'] ?? '%',
                        ]
                    );
                }
            }
        }

        $server->save();
    }

    public function detectAnomalies(Server $server): JsonResponse
    {
        $results = [];
        foreach (['cpu', 'ram', 'network'] as $type) {
            $results[$type] = $this->aiService->detectAnomaly($server, $type);
        }

        return response()->json($results);
    }
}