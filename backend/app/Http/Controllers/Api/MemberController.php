<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser || ! $authUser->current_organization_id) {
            return response()->json([
                'members' => [],
            ]);
        }

        $organization = $authUser->organizations()
            ->where('organizations.id', $authUser->current_organization_id)
            ->first();

        if (! $organization) {
            return response()->json([
                'members' => [],
            ]);
        }

        $members = $organization->users()
            ->withPivot('role_id')
            ->get()
            ->map(function ($member) {
                $roleId = $member->pivot?->role_id;
                $role = $roleId ? Role::find($roleId) : null;

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role_id' => $roleId,
                    'role_name' => $role?->name,
                    'role_slug' => $role?->slug,
                ];
            });

        return response()->json([
            'members' => $members,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $authUser = $request->user();

        if (! $authUser || ! $authUser->current_organization_id) {
            return response()->json([
                'message' => 'Aucune organisation active.',
            ], 400);
        }

        $organization = $authUser->organizations()
            ->where('organizations.id', $authUser->current_organization_id)
            ->first();

        if (! $organization) {
            return response()->json([
                'message' => 'Organisation introuvable.',
            ], 404);
        }

        $member = User::where('email', $request->email)->first();

        if (! $member) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $alreadyExists = $organization->users()
            ->where('users.id', $member->id)
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Ce membre appartient déjà à cette organisation.',
            ], 422);
        }

        $organization->users()->attach($member->id, [
            'role_id' => $request->role_id,
        ]);

        $role = Role::find($request->role_id);

        $this->notificationService->create(
            $member->id,
            $organization->id,
            'member_added',
            'Ajout à l’organisation',
            'Vous avez été ajouté à l’organisation '.$organization->name.' avec le rôle '.($role?->name ?? 'membre').'.'
        );

        return response()->json([
            'message' => 'Membre ajouté avec succès.',
        ], 201);
    }
}