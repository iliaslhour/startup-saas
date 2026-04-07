<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationMemberRequest;
use App\Http\Requests\Organization\UpdateOrganizationMemberRequest;
use App\Http\Resources\Organization\OrganizationMemberResource;
use App\Http\Resources\Organization\RoleResource;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Notification\NotificationService;

class OrganizationMemberController extends Controller
{
    public function index(Request $request, Organization $organization): JsonResponse
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $authUser->belongsToOrganization($organization->id)) {
            return response()->json([
                'message' => 'Accès non autorisé à cette organisation.',
            ], 403);
        }

        $members = $organization->members()->get()->map(function ($member) {
            if ($member->pivot) {
                $member->pivot->setRelation('role', Role::find($member->pivot->role_id));
            }

            return $member;
        });

        return response()->json([
            'members' => OrganizationMemberResource::collection($members),
        ]);
    }

    public function store(StoreOrganizationMemberRequest $request, Organization $organization): JsonResponse
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $authRole = $authUser->getRoleInOrganization($organization->id);

        if (! $authRole || $authRole->slug !== 'admin') {
            return response()->json([
                'message' => 'Seul un administrateur peut ajouter des membres.',
            ], 403);
        }

        $member = User::where('email', $request->string('email')->toString())->first();

        if (! $member) {
            return response()->json([
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        if ($member->belongsToOrganization($organization->id)) {
            return response()->json([
                'message' => 'Cet utilisateur appartient déjà à cette organisation.',
            ], 422);
        }

        $organization->members()->attach($member->id, [
            'role_id' => $request->integer('role_id'),
        ]);

        $organization->load(['members']);

        $freshMember = $organization->members()
            ->where('users.id', $member->id)
            ->first();

        if ($freshMember && $freshMember->pivot) {
            $freshMember->pivot->setRelation('role', Role::find($freshMember->pivot->role_id));
        }
        $this->notificationService->createForUser(
            $member->id,
            $organization->id,
            'organization_member_added',
            'Ajout à une organisation',
            'Vous avez été ajouté à l’organisation "'.$organization->name.'".',
            'organization',
            $organization->id
        );

        return response()->json([
            'message' => 'Membre ajouté avec succès.',
            'member' => new OrganizationMemberResource($freshMember),
        ], 201);
    }

    public function update(
        UpdateOrganizationMemberRequest $request,
        Organization $organization,
        User $user
    ): JsonResponse {
        /** @var \App\Models\User|null $authUser */
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $authRole = $authUser->getRoleInOrganization($organization->id);

        if (! $authRole || $authRole->slug !== 'admin') {
            return response()->json([
                'message' => 'Seul un administrateur peut modifier le rôle d’un membre.',
            ], 403);
        }

        if (! $user->belongsToOrganization($organization->id)) {
            return response()->json([
                'message' => 'Cet utilisateur n’appartient pas à cette organisation.',
            ], 404);
        }

        $organization->members()->updateExistingPivot($user->id, [
            'role_id' => $request->integer('role_id'),
        ]);

        $freshMember = $organization->members()
            ->where('users.id', $user->id)
            ->first();

        if ($freshMember && $freshMember->pivot) {
            $freshMember->pivot->setRelation('role', Role::find($freshMember->pivot->role_id));
        }
        $this->notificationService->createForUser(
            $user->id,
            $organization->id,
            'organization_role_updated',
            'Rôle modifié',
            'Votre rôle dans l’organisation "'.$organization->name.'" a été modifié.',
            'organization',
            $organization->id
        );

        return response()->json([
            'message' => 'Rôle du membre mis à jour avec succès.',
            'member' => new OrganizationMemberResource($freshMember),
        ]);
    }

    public function destroy(Request $request, Organization $organization, User $user): JsonResponse
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $authRole = $authUser->getRoleInOrganization($organization->id);

        if (! $authRole || $authRole->slug !== 'admin') {
            return response()->json([
                'message' => 'Seul un administrateur peut retirer un membre.',
            ], 403);
        }

        if ($organization->owner_id === $user->id) {
            return response()->json([
                'message' => 'Le propriétaire de l’organisation ne peut pas être supprimé.',
            ], 422);
        }

        if (! $user->belongsToOrganization($organization->id)) {
            return response()->json([
                'message' => 'Cet utilisateur n’appartient pas à cette organisation.',
            ], 404);
        }

        $organization->members()->detach($user->id);

        if ($user->current_organization_id === $organization->id) {
            $user->update([
                'current_organization_id' => null,
            ]);
        }
        $this->notificationService->createForUser(
            $user->id,
            $organization->id,
            'organization_member_removed',
            'Retrait de l’organisation',
            'Vous avez été retiré de l’organisation "'.$organization->name.'".',
            'organization',
            $organization->id
        );
        return response()->json([
            'message' => 'Membre retiré avec succès.',
        ]);
    }

    public function roles(Request $request, Organization $organization): JsonResponse
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $authUser->belongsToOrganization($organization->id)) {
            return response()->json([
                'message' => 'Accès non autorisé à cette organisation.',
            ], 403);
        }

        $roles = Role::query()->orderBy('id')->get();

        return response()->json([
            'roles' => RoleResource::collection($roles),
        ]);
    }
    

    public function __construct(protected NotificationService $notificationService){

    }
}