<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'organization_id' => $this['organization_id'],
            'organization_name' => $this['organization_name'],
            'projects_active' => $this['projects_active'],
            'projects_completed' => $this['projects_completed'],
            'tasks_total' => $this['tasks_total'],
            'tasks_done' => $this['tasks_done'],
            'tasks_in_progress' => $this['tasks_in_progress'],
            'tasks_todo' => $this['tasks_todo'],
            'tasks_overdue' => $this['tasks_overdue'],
            'completion_rate' => $this['completion_rate'],
        ];
    }
}