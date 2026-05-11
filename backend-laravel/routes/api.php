<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AnomalyController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\MetricController;
use App\Http\Controllers\Api\ZabbixController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\GrafanaController;
use App\Http\Controllers\Api\SyncController;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);
        Route::get('/dashboard/alerts', [DashboardController::class, 'recentAlerts']);
        Route::get('/dashboard/anomalies', [DashboardController::class, 'recentAnomalies']);
        Route::get('/dashboard/logs', [DashboardController::class, 'recentLogs']);
        Route::get('/dashboard/alerts-chart', [DashboardController::class, 'alertsChart']);
        Route::get('/dashboard/metrics-trend/{type}', [DashboardController::class, 'metricsTrend']);

        Route::apiResource('servers', ServerController::class);
        Route::post('/servers/sync-zabbix', [ServerController::class, 'syncFromZabbix']);
        Route::post('/servers/{server}/detect-anomalies', [ServerController::class, 'detectAnomalies']);

        Route::get('/alerts/new', [AlertController::class, 'newAlerts']);
        Route::apiResource('alerts', AlertController::class);
        Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);
        Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve']);

        Route::apiResource('anomalies', AnomalyController::class)->only(['index', 'show']);
        Route::post('/anomalies/{anomaly}/false-positive', [AnomalyController::class, 'markAsFalsePositive']);
        Route::post('/anomalies/detect', [AnomalyController::class, 'runDetection']);

        Route::apiResource('incidents', IncidentController::class);
        Route::post('/incidents/{incident}/resolve', [IncidentController::class, 'resolve']);

        Route::get('/logs', [LogController::class, 'index']);
        Route::get('/logs/critical', [LogController::class, 'critical']);
        Route::post('/logs/search-elastic', [LogController::class, 'searchElastic']);
        Route::get('/logs/elastic-critical', [LogController::class, 'elasticCritical']);
        Route::get('/logs/stats', [LogController::class, 'stats']);

        Route::get('/metrics', [MetricController::class, 'index']);
        Route::get('/servers/{server}/metrics', [MetricController::class, 'serverMetrics']);
        Route::post('/servers/{server}/sync-metrics', [MetricController::class, 'syncFromZabbix']);

        Route::get('/zabbix/hosts', [ZabbixController::class, 'hosts']);
        Route::get('/zabbix/triggers', [ZabbixController::class, 'triggers']);
        Route::get('/zabbix/problems', [ZabbixController::class, 'problems']);
        Route::get('/zabbix/availability', [ZabbixController::class, 'availability']);

        Route::get('/elastic/health', function () {
            $elastic = app(\App\Services\ElasticService::class);
            return response()->json($elastic->getClusterInfo());
        });

        Route::get('/grafana/health', [GrafanaController::class, 'health']);
        Route::get('/grafana/dashboards', [GrafanaController::class, 'dashboards']);
        Route::get('/grafana/dashboards/{uid}', [GrafanaController::class, 'dashboard']);
        Route::get('/grafana/datasources', [GrafanaController::class, 'datasources']);
        Route::get('/grafana/alerts', [GrafanaController::class, 'alerts']);

        Route::post('/sync/all', [SyncController::class, 'syncAll']);
        Route::post('/sync/zabbix', [SyncController::class, 'syncZabbix']);
        Route::get('/sync/elasticsearch', [SyncController::class, 'syncElasticsearch']);
        Route::get('/sync/grafana', [SyncController::class, 'checkGrafana']);

        Route::get('/ai/detect/{server}', [AiController::class, 'detect']);
        Route::post('/ai/detect-all', [AiController::class, 'detectAll']);
        Route::get('/ai/stats', [AiController::class, 'stats']);
        Route::get('/ai/health', [AiController::class, 'health']);

        Route::middleware('role:administrateur')->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
        });
    });
});