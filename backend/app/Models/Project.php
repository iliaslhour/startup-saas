<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
    ];
}