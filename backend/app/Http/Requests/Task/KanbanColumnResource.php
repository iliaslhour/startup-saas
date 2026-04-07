<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KanbanColumnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this['key'],
            'label' => $this['label'],
            'count' => count($this['tasks']),
            'tasks' => TaskResource::collection($this['tasks']),
        ];
    }
}