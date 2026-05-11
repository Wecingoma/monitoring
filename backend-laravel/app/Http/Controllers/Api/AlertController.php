<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlertRequest;
use App\Models\Alert;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function newAlerts(Request $request): JsonResponse
    {
        $since = $request->query('since', now()->subMinutes(2)->toDateTimeString());

        $alerts = Alert::with('server')
            ->where('created_at', '>', $since)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'severity' => $a->severity,
                'source' => $a->source,
                'server_name' => $a->server?->name,
                'created_at' => $a->created_at->toIso8601String(),
            ]);

        return response()->json($alerts);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Alert::with('server', 'user');

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $alerts = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($alerts);
    }

    public function store(AlertRequest $request): JsonResponse
    {
        $alert = Alert::create($request->validated());
        $this->auditService->logModel('alert_created', $alert);

        return response()->json($alert->load('server', 'user'), 201);
    }

    public function show(Alert $alert): JsonResponse
    {
        return response()->json($alert->load('server', 'user'));
    }

    public function acknowledge(Alert $alert): JsonResponse
    {
        $alert->acknowledge();
        $this->auditService->logModel('alert_acknowledged', $alert);

        return response()->json($alert->fresh()->load('server', 'user'));
    }

    public function resolve(Alert $alert): JsonResponse
    {
        $alert->resolve();
        $this->auditService->logModel('alert_resolved', $alert);

        return response()->json($alert->fresh()->load('server', 'user'));
    }
}