<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\AssignTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\Task\KanbanColumnResource;
use App\Http\Resources\Task\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Notification\NotificationService;

class TaskController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à ce projet.'], 403);
        }

        $tasks = $project->tasks()
            ->with(['creator', 'assignee', 'project'])
            ->latest()
            ->get();

        return response()->json([
            'tasks' => TaskResource::collection($tasks),
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $validated = $request->validated();
        $project = Project::find($validated['project_id']);

        if (! $project) {
            return response()->json(['message' => 'Projet introuvable.'], 404);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à ce projet.'], 403);
        }

        $role = $user->getRoleInOrganization($project->organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json(['message' => 'Vous n’êtes pas autorisé à créer une tâche.'], 403);
        }

        $assignedTo = $validated['assigned_to'] ?? null;

        if ($assignedTo) {
            $assignee = User::find($assignedTo);

            if (! $assignee || ! $assignee->belongsToOrganization($project->organization_id)) {
                return response()->json([
                    'message' => 'Le membre assigné n’appartient pas à l’organisation du projet.',
                ], 422);
            }

            if (! $project->members()->where('users.id', $assignedTo)->exists()) {
                return response()->json([
                    'message' => 'Le membre assigné n’appartient pas au projet.',
                ], 422);
            }
        }

        $task = Task::create([
            'project_id' => $project->id,
            'organization_id' => $project->organization_id,
            'created_by' => $user->id,
            'assigned_to' => $assignedTo,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'todo',
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $task->load(['creator', 'assignee', 'project']);

        if ($task->assigned_to) {
            $this->notificationService->createForUser(
                $task->assigned_to,
                $task->organization_id,
                'task_assigned',
                'Nouvelle tâche assignée',
                'La tâche "'.$task->title.'" vous a été assignée.',
                'task',
                $task->id
            );
        }

        return response()->json([
            'message' => 'Tâche créée avec succès.',
            'task' => new TaskResource($task),
        ], 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($task->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette tâche.'], 403);
        }

        $task->load(['creator', 'assignee', 'project']);

        return response()->json([
            'task' => new TaskResource($task),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($task->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette tâche.'], 403);
        }

        $role = $user->getRoleInOrganization($task->organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json(['message' => 'Vous n’êtes pas autorisé à modifier cette tâche.'], 403);
        }

        $validated = $request->validated();
        $assignedTo = $validated['assigned_to'] ?? null;

        if ($assignedTo) {
            $assignee = User::find($assignedTo);

            if (! $assignee || ! $assignee->belongsToOrganization($task->organization_id)) {
                return response()->json([
                    'message' => 'Le membre assigné n’appartient pas à l’organisation de cette tâche.',
                ], 422);
            }

            if (! $task->project->members()->where('users.id', $assignedTo)->exists()) {
                return response()->json([
                    'message' => 'Le membre assigné n’appartient pas au projet.',
                ], 422);
            }
        }

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'assigned_to' => $assignedTo,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $task->load(['creator', 'assignee', 'project']);

        return response()->json([
            'message' => 'Tâche mise à jour avec succès.',
            'task' => new TaskResource($task),
        ]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($task->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette tâche.'], 403);
        }

        $role = $user->getRoleInOrganization($task->organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json(['message' => 'Vous n’êtes pas autorisé à supprimer cette tâche.'], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Tâche supprimée avec succès.',
        ]);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($task->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette tâche.'], 403);
        }

        $role = $user->getRoleInOrganization($task->organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json(['message' => 'Vous n’êtes pas autorisé à changer le statut de cette tâche.'], 403);
        }

        $task->update([
            'status' => $request->string('status')->toString(),
        ]);

        $task->load(['creator', 'assignee', 'project']);
        $projectMemberIds = $task->project->members()->pluck('users.id')->toArray();
        $this->notificationService->notifyOrganizationMembersExcept(
            $task->organization_id,
            $projectMemberIds,
            $user->id,
            'task_status_updated',
            'Statut de tâche mis à jour',
            'Le statut de la tâche "'.$task->title.'" est maintenant "'.$task->status.'".',
            'task',
            $task->id
        );

        return response()->json([
            'message' => 'Statut de la tâche mis à jour avec succès.',
            'task' => new TaskResource($task),
        ]);
    }

    public function assign(AssignTaskRequest $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($task->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette tâche.'], 403);
        }

        $role = $user->getRoleInOrganization($task->organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json(['message' => 'Vous n’êtes pas autorisé à assigner cette tâche.'], 403);
        }

        $assignedTo = $request->integer('assigned_to');

        if ($assignedTo) {
            $assignee = User::find($assignedTo);

            if (! $assignee || ! $assignee->belongsToOrganization($task->organization_id)) {
                return response()->json([
                    'message' => 'Le membre assigné n’appartient pas à l’organisation.',
                ], 422);
            }

            if (! $task->project->members()->where('users.id', $assignedTo)->exists()) {
                return response()->json([
                    'message' => 'Le membre assigné n’appartient pas au projet.',
                ], 422);
            }
        }

        $task->update([
            'assigned_to' => $assignedTo,
        ]);

        $task->load(['creator', 'assignee', 'project']);
        
        if ($assignedTo) {
            $this->notificationService->createForUser(
                $assignedTo,
                $task->organization_id,
                'task_assigned',
                'Tâche assignée',
                'La tâche "'.$task->title.'" vous a été assignée.',
                'task',
                $task->id
            );
        }

        return response()->json([
            'message' => 'Affectation mise à jour avec succès.',
            'task' => new TaskResource($task),
        ]);
    }

    public function kanban(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à ce projet.'], 403);
        }

        $tasks = $project->tasks()
            ->with(['creator', 'assignee', 'project'])
            ->get()
            ->groupBy('status');

        $columns = collect([
            [
                'key' => 'todo',
                'label' => 'To Do',
                'tasks' => $tasks->get('todo', collect())->values(),
            ],
            [
                'key' => 'in_progress',
                'label' => 'In Progress',
                'tasks' => $tasks->get('in_progress', collect())->values(),
            ],
            [
                'key' => 'done',
                'label' => 'Done',
                'tasks' => $tasks->get('done', collect())->values(),
            ],
        ]);

        return response()->json([
            'project_id' => $project->id,
            'kanban' => KanbanColumnResource::collection($columns),
        ]);
    }
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }
}