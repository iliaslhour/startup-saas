<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
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
                'summary' => [
                    'projects_active' => 0,
                    'tasks_total' => 0,
                    'tasks_done' => 0,
                    'tasks_in_progress' => 0,
                    'tasks_overdue' => 0,
                    'completion_rate' => 0,
                    'members_count' => 0,
                    'invoices_count' => 0,
                    'notifications_unread' => 0,
                    'invoices_total_amount' => 0,
                ],
            ], 200);
        }

        $organizationId = $user->current_organization_id;

        $projectsActive = Project::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->count();

        $tasksTotal = Task::where('organization_id', $organizationId)->count();

        $tasksDone = Task::where('organization_id', $organizationId)
            ->where('status', 'done')
            ->count();

        $tasksInProgress = Task::where('organization_id', $organizationId)
            ->where('status', 'in_progress')
            ->count();

        $tasksOverdue = Task::where('organization_id', $organizationId)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'done')
            ->count();

        $completionRate = $tasksTotal > 0
            ? round(($tasksDone / $tasksTotal) * 100, 2)
            : 0;

        $membersCount = $user->organizations()
            ->where('organizations.id', $organizationId)
            ->first()?->users()?->count() ?? 0;

        $invoicesCount = Invoice::where('organization_id', $organizationId)->count();

        $notificationsUnread = Notification::where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $invoicesTotalAmount = Invoice::where('organization_id', $organizationId)
            ->sum('total_amount');

        return response()->json([
            'summary' => [
                'projects_active' => $projectsActive,
                'tasks_total' => $tasksTotal,
                'tasks_done' => $tasksDone,
                'tasks_in_progress' => $tasksInProgress,
                'tasks_overdue' => $tasksOverdue,
                'completion_rate' => $completionRate,
                'members_count' => $membersCount,
                'invoices_count' => $invoicesCount,
                'notifications_unread' => $notificationsUnread,
                'invoices_total_amount' => (float) $invoicesTotalAmount,
            ],
        ]);
    }
}