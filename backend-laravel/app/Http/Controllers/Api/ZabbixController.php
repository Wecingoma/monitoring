<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZabbixService;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZabbixController extends Controller
{
    public function __construct(
        private ZabbixService $zabbixService,
        private AuditService $auditService
    ) {}

    public function hosts(Request $request): JsonResponse
    {
        $params = $request->only(['groupids', 'hostids', 'filter']);
        $hosts = $this->zabbixService->getHosts($params);
        return response()->json($hosts);
    }

    public function triggers(Request $request): JsonResponse
    {
        $params = $request->only(['hostids', 'groupids', 'min_priority']);
        $triggers = $this->zabbixService->getTriggers($params);
        return response()->json($triggers);
    }

    public function problems(Request $request): JsonResponse
    {
        $params = $request->only(['hostids', 'groupids', 'recent']);
        $problems = $this->zabbixService->getProblems($params);
        return response()->json($problems);
    }

    public function availability(): JsonResponse
    {
        return response()->json($this->zabbixService->getHostAvailability());
    }
}