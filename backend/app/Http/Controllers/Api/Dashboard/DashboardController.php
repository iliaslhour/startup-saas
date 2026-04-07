<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->current_organization_id) {
            return response()->json([
                'message' => 'Aucune organisation active.',
            ], 400);
        }

        $organizationId = $user->current_organization_id;

        return response()->json([
            'projects_count' => Project::where('organization_id', $organizationId)->count(),
            'tasks_count' => Task::where('organization_id', $organizationId)->count(),
            'invoices_count' => Invoice::where('organization_id', $organizationId)->count(),

            'completed_tasks' => Task::where('organization_id', $organizationId)
                ->where('status', 'done')
                ->count(),

            'pending_tasks' => Task::where('organization_id', $organizationId)
                ->where('status', 'todo')
                ->count(),

            'invoices_paid' => Invoice::where('organization_id', $organizationId)
                ->where('status', 'paid')
                ->sum('amount'),

            'invoices_pending' => Invoice::where('organization_id', $organizationId)
                ->where('status', 'pending')
                ->sum('amount'),
        ]);
    }
}