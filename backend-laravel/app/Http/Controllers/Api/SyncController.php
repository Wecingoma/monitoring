<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZabbixService;
use App\Services\ElasticService;
use App\Services\GrafanaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class SyncController extends Controller
{
    public function __construct(
        private ZabbixService $zabbixService,
        private ElasticService $elasticService,
        private GrafanaService $grafanaService
    ) {}

    public function syncAll(): JsonResponse
    {
        $zabbixResult = $this->runZabbixSync();
        $esResult = $this->syncElasticsearch();

        return response()->json([
            'zabbix' => $zabbixResult,
            'elasticsearch' => $esResult,
            'grafana' => $this->checkGrafana(),
        ]);
    }

    public function syncZabbix(): JsonResponse
    {
        return response()->json($this->runZabbixSync());
    }

    private function runZabbixSync(): array
    {
        Artisan::call('sync:zabbix');
        Artisan::call('monitoring:check-thresholds');

        $hosts = $this->zabbixService->getHosts(['filter' => ['status' => 0]]);
        $problems = $this->zabbixService->getProblems();

        return [
            'connected' => !empty($hosts),
            'hosts' => count($hosts),
            'problems' => count($problems),
        ];
    }

    public function syncElasticsearch(): array
    {
        Artisan::call('sync:elasticsearch');

        return [
            'connected' => $this->elasticService->isAvailable(),
            'cluster' => $this->elasticService->getClusterInfo(),
        ];
    }

    public function checkGrafana(): array
    {
        $health = $this->grafanaService->getHealth();
        $dashboards = $this->grafanaService->getDashboards();
        $datasources = $this->grafanaService->getDatasources();

        return [
            'connected' => $this->grafanaService->isAvailable(),
            'health' => $health,
            'dashboards' => count($dashboards),
            'datasources' => count($datasources),
        ];
    }
}