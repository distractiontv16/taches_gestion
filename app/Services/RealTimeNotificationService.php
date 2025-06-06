<?php

namespace App\Services;

use App\Events\TaskStatusChanged;
use App\Events\TaskOverdue;
use App\Events\DashboardUpdated;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RealTimeNotificationService
{
    /**
     * Broadcast task status change event
     */
    public function broadcastTaskStatusChange(Task $task, string $oldStatus, string $newStatus): void
    {
        try {
            event(new TaskStatusChanged($task, $oldStatus, $newStatus));
            
            // Also update dashboard stats
            $this->broadcastDashboardUpdate($task->user_id);
            
            Log::info('Task status change broadcasted', [
                'task_id' => $task->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to broadcast task status change', [
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Broadcast task overdue event
     */
    public function broadcastTaskOverdue(Task $task): void
    {
        try {
            $overdueMinutes = $this->calculateOverdueMinutes($task);
            event(new TaskOverdue($task, $overdueMinutes));
            
            Log::info('Task overdue broadcasted', [
                'task_id' => $task->id,
                'overdue_minutes' => $overdueMinutes
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to broadcast task overdue', [
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Broadcast dashboard update
     */
    public function broadcastDashboardUpdate(int $userId): void
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return;
            }

            $stats = $this->calculateUserStats($user);
            event(new DashboardUpdated($userId, $stats));
            
            Log::info('Dashboard update broadcasted', [
                'user_id' => $userId,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to broadcast dashboard update', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate user statistics for real-time updates
     */
    private function calculateUserStats(User $user): array
    {
        $tasks = $user->tasks();
        $now = Carbon::now();

        // Basic task counts
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $pendingTasks = $tasks->where('status', '!=', 'completed')->count();
        
        // Overdue and upcoming tasks
        $overdueTasks = $tasks->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->get()
            ->filter(function ($task) use ($now) {
                return Carbon::parse($task->due_date)->isPast();
            })
            ->count();

        $upcomingTasks = $tasks->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->get()
            ->filter(function ($task) use ($now) {
                $dueDate = Carbon::parse($task->due_date);
                return $dueDate->isFuture() && $dueDate->diffInHours($now) <= 24;
            })
            ->count();

        // Priority breakdown
        $highPriorityTasks = $tasks->where('priority', 'high')->where('status', '!=', 'completed')->count();
        $mediumPriorityTasks = $tasks->where('priority', 'medium')->where('status', '!=', 'completed')->count();
        $lowPriorityTasks = $tasks->where('priority', 'low')->where('status', '!=', 'completed')->count();

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'overdue_tasks' => $overdueTasks,
            'upcoming_tasks' => $upcomingTasks,
            'high_priority_tasks' => $highPriorityTasks,
            'medium_priority_tasks' => $mediumPriorityTasks,
            'low_priority_tasks' => $lowPriorityTasks,
            'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
        ];
    }

    /**
     * Calculate overdue minutes for a task
     */
    private function calculateOverdueMinutes(Task $task): int
    {
        if (!$task->due_date) {
            return 0;
        }

        $now = Carbon::now();
        $dueDate = Carbon::parse($task->due_date);
        
        return max(0, $now->diffInMinutes($dueDate));
    }

    /**
     * Get badge data for real-time updates
     */
    public function getBadgeData(User $user): array
    {
        $tasks = $user->tasks()->where('status', '!=', 'completed')->get();
        $now = Carbon::now();

        $pendingTasks = $tasks->filter(function ($task) use ($now) {
            return !$task->due_date || Carbon::parse($task->due_date)->isFuture();
        });

        $overdueTasks = $tasks->filter(function ($task) use ($now) {
            return $task->due_date && Carbon::parse($task->due_date)->isPast();
        });

        return [
            'pending_count' => $pendingTasks->count(),
            'overdue_count' => $overdueTasks->count(),
            'total_count' => $tasks->count(),
            'pending_tasks' => $pendingTasks->take(5)->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date?->format('Y-m-d H:i'),
                ];
            }),
            'overdue_tasks' => $overdueTasks->take(5)->map(function ($task) use ($now) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date?->format('Y-m-d H:i'),
                    'overdue_minutes' => $task->due_date ? $now->diffInMinutes(Carbon::parse($task->due_date)) : 0,
                ];
            }),
        ];
    }
}
