<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectMemberRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectMemberResource;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Notification\NotificationService;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->current_organization_id) {
            return response()->json([
                'message' => 'Aucune organisation active sélectionnée.',
            ], 422);
        }

        $projects = Project::query()
            ->where('organization_id', $user->current_organization_id)
            ->with(['creator', 'organization', 'members'])
            ->latest()
            ->get();

        return response()->json([
            'projects' => ProjectResource::collection($projects),
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {

            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->current_organization_id) {
            return response()->json([
                'message' => 'Aucune organisation active sélectionnée.',
            ], 422);
        }

        $role = $user->getRoleInOrganization($user->current_organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json([
                'message' => 'Vous n’êtes pas autorisé à créer un projet.',
            ], 403);
        }

        $validated = $request->validated();

        $project = Project::create([
            'organization_id' => $user->current_organization_id,
            'created_by' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        $project->members()->syncWithoutDetaching([$user->id]);

        if (! empty($validated['member_ids'])) {
            $allowedMemberIds = User::query()
                ->whereIn('id', $validated['member_ids'])
                ->whereHas('organizations', function ($query) use ($user) {
                    $query->where('organizations.id', $user->current_organization_id);
                })
                ->pluck('id')
                ->toArray();

            if (! empty($allowedMemberIds)) {
                $project->members()->syncWithoutDetaching($allowedMemberIds);
            }
        }

        $project->load(['creator', 'organization', 'members']);

        $memberIds = $project->members()->pluck('users.id')->toArray();
        $this->notificationService->notifyOrganizationMembersExcept(
            $project->organization_id,
            $memberIds,
            $user->id,
            'project_created',
            'Nouveau projet',
            'Le projet "'.$project->name.'" a été créé.',
            'project',
            $project->id
        );

        return response()->json([
            'message' => 'Projet créé avec succès.',
            'project' => new ProjectResource($project),
        ], 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json([
                'message' => 'Accès non autorisé à ce projet.',
            ], 403);
        }

        $project->load(['creator', 'organization', 'members']);

        return response()->json([
            'project' => new ProjectResource($project),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json([
                'message' => 'Accès non autorisé à ce projet.',
            ], 403);
        }

        $role = $user->getRoleInOrganization($project->organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json([
                'message' => 'Vous n’êtes pas autorisé à modifier ce projet.',
            ], 403);
        }

        $project->update($request->validated());
        $project->load(['creator', 'organization', 'members']);

        return response()->json([
            'message' => 'Projet mis à jour avec succès.',
            'project' => new ProjectResource($project),
        ]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json([
                'message' => 'Accès non autorisé à ce projet.',
            ], 403);
        }

        $role = $user->getRoleInOrganization($project->organization_id);

        if (! $role || $role->slug !== 'admin') {
            return response()->json([
                'message' => 'Seul un administrateur peut supprimer un projet.',
            ], 403);
        }

        $project->delete();

        return response()->json([
            'message' => 'Projet supprimé avec succès.',
        ]);
    }

    public function members(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json([
                'message' => 'Accès non autorisé à ce projet.',
            ], 403);
        }

        $members = $project->members()->get();

        return response()->json([
            'members' => ProjectMemberResource::collection($members),
        ]);
    }

    public function addMember(
        StoreProjectMemberRequest $request,
        Project $project
    ): JsonResponse {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json([
                'message' => 'Accès non autorisé à ce projet.',
            ], 403);
        }

        $role = $user->getRoleInOrganization($project->organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json([
                'message' => 'Vous n’êtes pas autorisé à ajouter des membres au projet.',
            ], 403);
        }

        $memberId = $request->integer('user_id');
        $member = User::find($memberId);

        if (! $member || ! $member->belongsToOrganization($project->organization_id)) {
            return response()->json([
                'message' => 'Cet utilisateur n’appartient pas à l’organisation du projet.',
            ], 422);
        }

        if ($project->members()->where('users.id', $memberId)->exists()) {
            return response()->json([
                'message' => 'Cet utilisateur appartient déjà au projet.',
            ], 422);
        }

        $project->members()->attach($memberId);

        $freshMember = $project->members()->where('users.id', $memberId)->first();
        $this->notificationService->createForUser(
            $memberId,
            $project->organization_id,
            'project_member_added',
            'Ajout à un projet',
            'Vous avez été ajouté au projet "'.$project->name.'".',
            'project',
            $project->id
        );

        return response()->json([
            'message' => 'Membre ajouté au projet avec succès.',
            'member' => new ProjectMemberResource($freshMember),
        ], 201);
    }

    public function removeMember(Request $request, Project $project, User $member): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->belongsToOrganization($project->organization_id)) {
            return response()->json([
                'message' => 'Accès non autorisé à ce projet.',
            ], 403);
        }

        $role = $user->getRoleInOrganization($project->organization_id);

        if (! $role || ! in_array($role->slug, ['admin', 'developer'], true)) {
            return response()->json([
                'message' => 'Vous n’êtes pas autorisé à retirer des membres du projet.',
            ], 403);
        }

        if (! $project->members()->where('users.id', $member->id)->exists()) {
            return response()->json([
                'message' => 'Cet utilisateur n’appartient pas à ce projet.',
            ], 404);
        }

        $project->members()->detach($member->id);

        $memberIds = $project->members()->pluck('users.id')->toArray();
        $this->notificationService->notifyOrganizationMembersExcept(
            $project->organization_id,
            $memberIds,
            $user->id,
            'project_created',
            'Nouveau projet',
            'Le projet "'.$project->name.'" a été créé.',
            'project',
            $project->id
        );

        return response()->json([
            'message' => 'Membre retiré du projet avec succès.',
        ]);
    }
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }
}