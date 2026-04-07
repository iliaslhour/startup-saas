<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens; // 🔥 IMPORTANT
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // 🔥 IMPORTANT

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ===============================
    // RELATIONS
    // ===============================

    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }


    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }


    // ===============================
    // HELPERS
    // ===============================

    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organizations()
            ->where('organizations.id', $organizationId)
            ->exists();
    }

    public function getRoleInOrganization(int $organizationId)
    {
        $organization = $this->organizations()
            ->where('organizations.id', $organizationId)
            ->first();

        return $organization?->pivot?->role_id;
    }
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')->withPivot('role_id')->withTimestamps();
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}