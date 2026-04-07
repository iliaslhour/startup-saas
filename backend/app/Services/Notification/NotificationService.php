<?php

namespace App\Services\Notification;

use App\Models\Notification;

class NotificationService
{
    public function create(
        int $userId,
        ?int $organizationId,
        string $type,
        string $title,
        string $message
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
            'read_at' => null,
        ]);
    }
}