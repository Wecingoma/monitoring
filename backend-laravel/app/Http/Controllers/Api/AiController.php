<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use App\Models\Server;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    public function __construct(
        private AiService $aiService
    ) {}

    public function detect(Server $server): JsonResponse
    {
        $type = request()->input('type', 'cpu');
        $result = $this->aiService->detectAnomaly($server, $type);
        return response()->json($result);
    }

    public function detectAll(): JsonResponse
    {
        $results = $this->aiService->detectAllAnomalies();
        return response()->json($results);
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->aiService->getAnomalyStats());
    }

    public function health(): JsonResponse
    {
        return response()->json($this->aiService->getHealthStatus());
    }
}