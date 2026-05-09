<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GrafanaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrafanaController extends Controller
{
    public function __construct(
        private GrafanaService $grafanaService
    ) {}

    public function health(): JsonResponse
    {
        return response()->json($this->grafanaService->getHealth());
    }

    public function dashboards(): JsonResponse
    {
        return response()->json([
            'available' => $this->grafanaService->isAvailable(),
            'dashboards' => $this->grafanaService->getDashboards(),
        ]);
    }

    public function dashboard(string $uid): JsonResponse
    {
        return response()->json($this->grafanaService->getDashboard($uid));
    }

    public function datasources(): JsonResponse
    {
        return response()->json([
            'available' => $this->grafanaService->isAvailable(),
            'datasources' => $this->grafanaService->getDatasources(),
        ]);
    }

    public function alerts(): JsonResponse
    {
        return response()->json([
            'available' => $this->grafanaService->isAvailable(),
            'alerts' => $this->grafanaService->getAlerts(),
        ]);
    }
}