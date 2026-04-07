<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function organizationUsers(): HasMany
    {
        return $this->hasMany(OrganizationUser::class);
    }

    public static function admin(): ?self
    {
        return self::where('slug', 'admin')->first();
    }

    public static function developer(): ?self
    {
        return self::where('slug', 'developer')->first();
    }

    public static function client(): ?self
    {
        return self::where('slug', 'client')->first();
    }
}