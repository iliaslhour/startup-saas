<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'organization_id',
        'created_by',
        'invoice_number',
        'client_name',
        'client_email',
        'issue_date',
        'due_date',
        'tax_amount',
        'total_amount',
        'status',
    ];
}