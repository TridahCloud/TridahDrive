<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $projectId;
    public $oldStatusId;
    public $newStatusId;
    public $newSortOrder;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, int $oldStatusId, int $newStatusId, int $newSortOrder)
    {
        $this->task = $task;
        $this->projectId = $task->project_id;
        $this->oldStatusId = $oldStatusId;
        $this->newStatusId = $newStatusId;
        $this->newSortOrder = $newSortOrder;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('project.' . $this->projectId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.moved';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $this->task->load([
            'status:id,name,slug,color',
            'owner:id,name,email',
            'creator:id,name,email',
            'members:id,name,email',
            'labels:id,name,color',
            'customFieldValues.fieldDefinition:id,name,type',
        ]);

        return [
            'task' => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'status' => $this->task->status ? [
                    'id' => $this->task->status->id,
                    'name' => $this->task->status->name,
                    'slug' => $this->task->status->slug ?? '',
                    'color' => $this->task->status->color,
                ] : null,
                'sort_order' => $this->task->sort_order,
            ],
            'old_status_id' => $this->oldStatusId,
            'new_status_id' => $this->newStatusId,
            'new_sort_order' => $this->newSortOrder,
        ];
    }
}
