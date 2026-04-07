<?php

namespace App\Services\Organization;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrganizationService
{
    public function createOrganizationForUser(User $user, array $data): Organization
    {
        return DB::transaction(function () use ($user, $data) {
            $adminRole = Role::where('slug', 'admin')->first();

            if (! $adminRole) {
                throw new RuntimeException('Le rôle admin est introuvable. Vérifie les seeders.');
            }

            $organization = Organization::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'owner_id' => $user->id,
            ]);

            $organization->members()->attach($user->id, [
                'role_id' => $adminRole->id,
            ]);

            $user->update([
                'current_organization_id' => $organization->id,
            ]);

            return $organization->load(['owner', 'members']);
        });
    }

    public function switchOrganization(User $user, Organization $organization): User
    {
        if (! $user->belongsToOrganization($organization->id)) {
            throw new RuntimeException('Vous n’appartenez pas à cette organisation.');
        }

        $user->update([
            'current_organization_id' => $organization->id,
        ]);

        return $user->fresh(['organizations', 'currentOrganization']);
    }
}