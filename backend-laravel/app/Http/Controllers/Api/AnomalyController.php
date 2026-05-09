<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anomaly;
use App\Services\AiService;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnomalyController extends Controller
{
    public function __construct(
        private AiService $aiService,
        private AuditService $auditService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Anomaly::with('server');

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $query->notFalsePositive();

        $anomalies = $query->orderBy('detected_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($anomalies);
    }

    public function show(Anomaly $anomaly): JsonResponse
    {
        return response()->json($anomaly->load('server'));
    }

    public function markAsFalsePositive(Anomaly $anomaly): JsonResponse
    {
        $anomaly->markAsFalsePositive();
        $this->auditService->logModel('anomaly_marked_false_positive', $anomaly);

        return response()->json($anomaly->fresh()->load('server'));
    }

    public function runDetection(Request $request): JsonResponse
    {
        $results = $this->aiService->detectAllAnomalies();
        return response()->json($results);
    }
}