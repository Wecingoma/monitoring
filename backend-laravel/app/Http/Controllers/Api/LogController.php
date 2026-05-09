<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Services\ElasticService;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __construct(
        private ElasticService $elasticService,
        private AuditService $auditService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = SystemLog::with('server');

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $logs = $query->orderBy('logged_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($logs);
    }

    public function searchElastic(Request $request): JsonResponse
    {
        $results = $this->elasticService->searchLogs([
            'query' => $request->input('query', '*'),
            'from' => $request->input('from', 0),
            'size' => $request->input('size', 50),
            'filters' => $request->input('filters', []),
            'sort' => $request->input('sort', ['@timestamp' => ['order' => 'desc']]),
        ]);

        return response()->json($results);
    }

    public function critical(): JsonResponse
    {
        $logs = SystemLog::with('server')
            ->critical()
            ->recent()
            ->orderBy('logged_at', 'desc')
            ->paginate(50);

        return response()->json($logs);
    }

    public function elasticCritical(): JsonResponse
    {
        $hours = request()->input('hours', 24);
        $logs = $this->elasticService->getCriticalLogs($hours);

        return response()->json($logs);
    }

    public function stats(): JsonResponse
    {
        $hours = request()->input('hours', 24);
        return response()->json($this->elasticService->getLogStats($hours));
    }
}