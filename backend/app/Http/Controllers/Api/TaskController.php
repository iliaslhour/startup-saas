<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
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
                'tasks' => [],
            ]);
        }

        $tasks = Task::with(['project', 'assignee'])
            ->where('organization_id', $user->current_organization_id)
            ->latest()
            ->get();

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:todo,in_progress,done'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);

        $user = $request->user();

        if (! $user || ! $user->current_organization_id) {
            return response()->json([
                'message' => 'Aucune organisation active.',
            ], 400);
        }

        $project = Project::where('id', $request->project_id)
            ->where('organization_id', $user->current_organization_id)
            ->first();

        if (! $project) {
            return response()->json([
                'message' => 'Projet introuvable dans l’organisation active.',
            ], 404);
        }

        $task = Task::create([
            'organization_id' => $user->current_organization_id,
            'project_id' => $request->project_id,
            'created_by' => $user->id,
            'assigned_to' => $request->assigned_to,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        if ($request->assigned_to) {
            $this->notificationService->create(
                (int) $request->assigned_to,
                $user->current_organization_id,
                'task_assigned',
                'Nouvelle tâche assignée',
                'La tâche '.$task->title.' vous a été assignée.'
            );
        }

        return response()->json([
            'message' => 'Tâche créée avec succès.',
            'task' => $task->load(['project', 'assignee']),
        ], 201);
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:todo,in_progress,done'],
        ]);

        $user = $request->user();

        if (! $user || $task->organization_id !== $user->current_organization_id) {
            return response()->json([
                'message' => 'Accès non autorisé à cette tâche.',
            ], 403);
        }

        $task->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'task' => $task->fresh(['project', 'assignee']),
        ]);
    }

    public function byProject(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (! $user || $project->organization_id !== $user->current_organization_id) {
            return response()->json([
                'message' => 'Accès non autorisé à ce projet.',
            ], 403);
        }

        $tasks = Task::with(['assignee'])
            ->where('project_id', $project->id)
            ->latest()
            ->get();

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    public function kanban(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (! $user || $project->organization_id !== $user->current_organization_id) {
            return response()->json([
                'message' => 'Accès non autorisé à ce projet.',
            ], 403);
        }

        $tasks = Task::with(['assignee'])
            ->where('project_id', $project->id)
            ->get();

        $kanban = [
            [
                'key' => 'todo',
                'label' => 'À faire',
                'count' => $tasks->where('status', 'todo')->count(),
                'tasks' => $tasks->where('status', 'todo')->values(),
            ],
            [
                'key' => 'in_progress',
                'label' => 'En cours',
                'count' => $tasks->where('status', 'in_progress')->count(),
                'tasks' => $tasks->where('status', 'in_progress')->values(),
            ],
            [
                'key' => 'done',
                'label' => 'Terminée',
                'count' => $tasks->where('status', 'done')->count(),
                'tasks' => $tasks->where('status', 'done')->values(),
            ],
        ];

        return response()->json([
            'kanban' => $kanban,
        ]);
    }
}