<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServerRequest;
use App\Models\Server;
use App\Services\AuditService;
use App\Services\ZabbixService;
use App\Services\AiService;
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
        $hosts = $this->zabbixService->getHosts();
        $synced = 0;

        foreach ($hosts as $host) {
            Server::updateOrCreate(
                ['zabbix_host_id' => $host['hostid']],
                [
                    'name' => $host['name'],
                    'hostname' => $host['host'],
                    'ip_address' => $host['interfaces'][0]['ip'] ?? '0.0.0.0',
                    'status' => $host['status'] == 0 ? 'online' : 'offline',
                ]
            );
            $synced++;
        }

        $this->auditService->log('zabbix_sync', "Synchronisé {$synced} serveurs depuis Zabbix");

        return response()->json(['synced' => $synced, 'total' => count($hosts)]);
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