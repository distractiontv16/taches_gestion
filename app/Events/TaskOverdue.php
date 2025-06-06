<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TaskOverdue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $userId;
    public $overdueMinutes;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, int $overdueMinutes = 0)
    {
        $this->task = $task;
        $this->userId = $task->user_id;
        $this->overdueMinutes = $overdueMinutes;

        Log::info('TaskOverdue event created', [
            'task_id' => $task->id,
            'task_title' => $task->title,
            'user_id' => $this->userId,
            'overdue_minutes' => $overdueMinutes
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.overdue';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'status' => $this->task->status,
                'priority' => $this->task->priority,
                'due_date' => $this->task->due_date?->format('Y-m-d H:i:s'),
                'is_overdue' => true,
                'overdue_minutes' => $this->overdueMinutes,
            ],
            'message' => "La tâche '{$this->task->title}' est en retard de {$this->overdueMinutes} minutes",
            'timestamp' => now()->toISOString(),
        ];
    }
}
