<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncidentRequest;
use App\Models\Incident;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Incident::with('assignedTo', 'createdBy');

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $incidents = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($incidents);
    }

    public function store(IncidentRequest $request): JsonResponse
    {
        $incident = Incident::create(array_merge(
            $request->validated(),
            ['created_by' => auth()->id()]
        ));
        $this->auditService->logModel('incident_created', $incident);

        return response()->json($incident->load('assignedTo', 'createdBy'), 201);
    }

    public function show(Incident $incident): JsonResponse
    {
        return response()->json($incident->load('assignedTo', 'createdBy'));
    }

    public function update(IncidentRequest $request, Incident $incident): JsonResponse
    {
        $incident->update($request->validated());
        $this->auditService->logModel('incident_updated', $incident);

        return response()->json($incident->fresh()->load('assignedTo', 'createdBy'));
    }

    public function resolve(Incident $incident, Request $request): JsonResponse
    {
        $incident->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution' => $request->input('resolution'),
            'root_cause' => $request->input('root_cause'),
        ]);
        $this->auditService->logModel('incident_resolved', $incident);

        return response()->json($incident->fresh()->load('assignedTo', 'createdBy'));
    }
}