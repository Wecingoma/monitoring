<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->dashboardService->getFullDashboard());
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->dashboardService->getOverviewStats());
    }

    public function metrics(): JsonResponse
    {
        return response()->json($this->dashboardService->getSystemMetrics());
    }

    public function recentAlerts(): JsonResponse
    {
        return response()->json($this->dashboardService->getRecentAlerts());
    }

    public function recentAnomalies(): JsonResponse
    {
        return response()->json($this->dashboardService->getRecentAnomalies());
    }

    public function recentLogs(): JsonResponse
    {
        return response()->json($this->dashboardService->getRecentLogs());
    }

    public function alertsChart(): JsonResponse
    {
        return response()->json($this->dashboardService->getAlertsBySeverityChart());
    }

    public function metricsTrend(string $type): JsonResponse
    {
        return response()->json($this->dashboardService->getMetricsTrend($type));
    }
}