<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $projectId;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->projectId = $task->project_id;
        
        \Log::info('TaskCreated event constructed', [
            'task_id' => $task->id,
            'project_id' => $this->projectId,
            'channel' => 'project.' . $this->projectId,
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channel = new PrivateChannel('project.' . $this->projectId);
        
        \Log::info('TaskCreated broadcasting on channel', [
            'channel_name' => 'project.' . $this->projectId,
            'full_channel' => 'private-project.' . $this->projectId,
            'task_id' => $this->task->id,
            'broadcast_connection' => config('broadcasting.default'),
        ]);
        
        return [$channel];
    }
    
    /**
     * Determine if this event should be broadcast.
     */
    public function shouldBroadcast(): bool
    {
        \Log::info('TaskCreated shouldBroadcast check', [
            'task_id' => $this->task->id,
            'project_id' => $this->projectId,
        ]);
        return true;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.created';
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
                'description' => $this->task->description,
                'priority' => $this->task->priority,
                'due_date' => $this->task->due_date ? $this->task->due_date->format('Y-m-d') : null,
                'start_date' => $this->task->start_date ? $this->task->start_date->format('Y-m-d') : null,
                'estimated_hours' => $this->task->estimated_hours,
                'actual_hours' => $this->task->actual_hours,
                'sort_order' => $this->task->sort_order,
                'status' => $this->task->status ? [
                    'id' => $this->task->status->id,
                    'name' => $this->task->status->name,
                    'slug' => $this->task->status->slug ?? '',
                    'color' => $this->task->status->color,
                ] : null,
                'owner' => $this->task->owner ? [
                    'id' => $this->task->owner->id,
                    'name' => $this->task->owner->name,
                    'email' => $this->task->owner->email,
                ] : null,
                'members' => $this->task->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                    ];
                }),
                'labels' => $this->task->labels->map(function ($label) {
                    return [
                        'id' => $label->id,
                        'name' => $label->name,
                        'color' => $label->color,
                    ];
                }),
                'custom_fields' => $this->task->customFieldValues->mapWithKeys(function ($value) {
                    return [
                        $value->fieldDefinition->id => [
                            'id' => $value->fieldDefinition->id,
                            'name' => $value->fieldDefinition->name,
                            'type' => $value->fieldDefinition->type,
                            'value' => $value->value,
                        ],
                    ];
                }),
            ],
        ];
    }
}
