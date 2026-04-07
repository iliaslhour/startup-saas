<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->current_organization_id) {
            return response()->json([
                'projects' => [],
            ]);
        }

        $projects = Project::where('organization_id', $user->current_organization_id)
            ->latest()
            ->get();

        return response()->json([
            'projects' => $projects,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,completed,on_hold'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $user = $request->user();

        if (! $user || ! $user->current_organization_id) {
            return response()->json([
                'message' => 'Aucune organisation active.',
            ], 400);
        }

        $project = Project::create([
            'organization_id' => $user->current_organization_id,
            'created_by' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        $this->notificationService->create(
            $user->id,
            $user->current_organization_id,
            'project_created',
            'Projet créé',
            'Le projet '.$project->name.' a été créé avec succès.'
        );

        return response()->json([
            'message' => 'Projet créé avec succès.',
            'project' => $project,
        ], 201);
    }
}