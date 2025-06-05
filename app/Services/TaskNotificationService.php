<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class TaskNotificationService
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Send notification when a task is assigned to a user
     */
    public function notifyTaskAssignment(Task $task, User $assignedUser, bool $isReassignment = false)
    {
        try {
            // Send WhatsApp notification if user has WhatsApp number
            if ($assignedUser->whatsapp_number) {
                $message = $this->buildTaskAssignmentMessage($task, $assignedUser, $isReassignment);
                $this->whatsAppService->sendMessage($assignedUser->whatsapp_number, $message);

                Log::info('Task assignment notification sent via WhatsApp', [
                    'task_id' => $task->id,
                    'assigned_to' => $assignedUser->id,
                    'whatsapp_number' => $assignedUser->whatsapp_number,
                    'is_reassignment' => $isReassignment
                ]);
            }

            // You can add email notification here if needed
            // Mail::to($assignedUser->email)->send(new TaskAssignedMail($task));

        } catch (\Exception $e) {
            Log::error('Failed to send task assignment notification', [
                'task_id' => $task->id,
                'assigned_to' => $assignedUser->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Build WhatsApp message for task assignment
     */
    private function buildTaskAssignmentMessage(Task $task, User $assignedUser, bool $isReassignment = false): string
    {
        $message = $isReassignment ? "🔄 *Tâche réassignée*\n\n" : "🎯 *Nouvelle tâche assignée*\n\n";
        $message .= "Bonjour {$assignedUser->name},\n\n";

        if ($isReassignment) {
            $message .= "Une tâche vous a été réassignée :\n\n";
        } else {
            $message .= "Une nouvelle tâche vous a été assignée :\n\n";
        }

        $message .= "📋 *Titre :* {$task->title}\n";

        if ($task->description) {
            $message .= "📝 *Description :* {$task->description}\n";
        }

        $message .= "⚡ *Priorité :* " . $this->getPriorityText($task->priority) . "\n";
        $message .= "📊 *Statut :* " . $this->getStatusText($task->status) . "\n";

        if ($task->due_date) {
            $message .= "📅 *Échéance :* " . $task->due_date->format('d/m/Y H:i') . "\n";
        }

        $message .= "\n✅ Connectez-vous à l'application pour voir les détails et gérer cette tâche.";

        return $message;
    }

    /**
     * Get priority text in French
     */
    private function getPriorityText(string $priority): string
    {
        return match($priority) {
            'low' => '🟢 Faible',
            'medium' => '🟡 Moyenne',
            'high' => '🔴 Haute',
            default => $priority
        };
    }

    /**
     * Send notification when task status changes
     */
    public function notifyTaskStatusChange(Task $task, string $oldStatus, string $newStatus)
    {
        try {
            // Notify the task creator if someone else changed the status
            if ($task->assigned_to && $task->assigned_to !== $task->user_id) {
                $assignedUser = $task->assignedUser;
                
                if ($assignedUser && $assignedUser->whatsapp_number) {
                    $message = $this->buildStatusChangeMessage($task, $oldStatus, $newStatus);
                    $this->whatsAppService->sendMessage($assignedUser->whatsapp_number, $message);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send task status change notification', [
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Build WhatsApp message for status change
     */
    private function buildStatusChangeMessage(Task $task, string $oldStatus, string $newStatus): string
    {
        $message = "📊 *Mise à jour de tâche*\n\n";
        $message .= "La tâche \"{$task->title}\" a changé de statut :\n\n";
        $message .= "📋 *Ancien statut :* " . $this->getStatusText($oldStatus) . "\n";
        $message .= "✅ *Nouveau statut :* " . $this->getStatusText($newStatus) . "\n";
        
        return $message;
    }

    /**
     * Get status text in French
     */
    private function getStatusText(string $status): string
    {
        return match($status) {
            'to_do' => '📝 À faire',
            'in_progress' => '⚡ En cours',
            'completed' => '✅ Terminé',
            default => $status
        };
    }
}
