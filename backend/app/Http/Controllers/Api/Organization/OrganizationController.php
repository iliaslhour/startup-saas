<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\SwitchOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\Organization\OrganizationResource;
use App\Models\Organization;
use App\Services\Organization\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class OrganizationController extends Controller
{
    public function __construct(
        protected OrganizationService $organizationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $organizations = $user->organizations()
            ->with(['owner', 'members'])
            ->get();

        return response()->json([
            'organizations' => OrganizationResource::collection($organizations),
            'current_organization_id' => $user->current_organization_id,
        ]);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $organization = $this->organizationService->createOrganizationForUser(
            $user,
            $request->validated()
        );

        return response()->json([
            'message' => 'Organisation créée avec succès.',
            'organization' => new OrganizationResource($organization),
        ], 201);
    }

    public function show(Request $request, Organization $organization): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        if (! $user->belongsToOrganization($organization->id)) {
            return response()->json([
                'message' => 'Accès non autorisé à cette organisation.',
            ], 403);
        }

        $organization->load(['owner', 'members']);

        return response()->json([
            'organization' => new OrganizationResource($organization),
        ]);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $role = $user->getRoleInOrganization($organization->id);

        if (! $role || $role->slug !== 'admin') {
            return response()->json([
                'message' => 'Seul un administrateur peut modifier cette organisation.',
            ], 403);
        }

        $organization->update($request->validated());
        $organization->load(['owner', 'members']);

        return response()->json([
            'message' => 'Organisation mise à jour avec succès.',
            'organization' => new OrganizationResource($organization),
        ]);
    }

    public function switch(SwitchOrganizationRequest $request, Organization $organization): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        try {
            $user = $this->organizationService->switchOrganization($user, $organization);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => 'Organisation active changée avec succès.',
            'user' => new UserResource($user),
        ]);
    }
}