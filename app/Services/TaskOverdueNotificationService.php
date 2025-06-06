<?php

namespace App\Services;

use App\Models\Task;
use App\Mail\TaskReminderMail;
use App\Services\RealTimeNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;

/**
 * Service dédié à la gestion des notifications de tâches en retard
 * Respecte les spécifications SoNaMA IT : notifications 30 minutes APRÈS l'échéance
 */
class TaskOverdueNotificationService
{
    protected $realTimeNotificationService;

    public function __construct(RealTimeNotificationService $realTimeNotificationService)
    {
        $this->realTimeNotificationService = $realTimeNotificationService;
    }

    /**
     * Délai en minutes après l'échéance pour envoyer la notification
     */
    const OVERDUE_NOTIFICATION_DELAY_MINUTES = 30;

    /**
     * Fenêtre de tolérance en minutes pour la détection des tâches éligibles
     */
    const DETECTION_WINDOW_MINUTES = 5;

    /**
     * Trouve et traite toutes les tâches éligibles pour une notification de retard
     *
     * @return array Statistiques d'envoi
     */
    public function processOverdueTasks(): array
    {
        $stats = [
            'processed' => 0,
            'sent' => 0,
            'errors' => 0,
            'already_notified' => 0
        ];

        $overdueTasks = $this->findEligibleOverdueTasks();
        $stats['processed'] = $overdueTasks->count();

        Log::info("TaskOverdueNotificationService: {$stats['processed']} tâches éligibles trouvées");

        foreach ($overdueTasks as $task) {
            try {
                if ($this->sendOverdueNotification($task)) {
                    $stats['sent']++;
                } else {
                    $stats['already_notified']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("Erreur lors de l'envoi de notification pour la tâche {$task->id}: " . $e->getMessage());
            }
        }

        Log::info("TaskOverdueNotificationService: Statistiques d'envoi", $stats);
        return $stats;
    }

    /**
     * Trouve les tâches éligibles pour une notification de retard
     * Critères: 
     * - Non complétées
     * - Avec date d'échéance
     * - Échéance dépassée de 30 minutes (±5 minutes de tolérance)
     * - Notification pas encore envoyée
     *
     * @return Collection
     */
    public function findEligibleOverdueTasks(): Collection
    {
        $now = Carbon::now();
        
        // Calcul de la fenêtre de détection
        // Tâches dues il y a 30 minutes (±5 minutes de tolérance)
        $targetOverdueTime = $now->copy()->subMinutes(self::OVERDUE_NOTIFICATION_DELAY_MINUTES);
        $windowStart = $targetOverdueTime->copy()->subMinutes(self::DETECTION_WINDOW_MINUTES);
        $windowEnd = $targetOverdueTime->copy()->addMinutes(self::DETECTION_WINDOW_MINUTES);

        Log::info("Recherche des tâches en retard dans la fenêtre: {$windowStart->format('Y-m-d H:i:s')} à {$windowEnd->format('Y-m-d H:i:s')}");

        return Task::whereIn('status', ['to_do', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('overdue_notification_sent', false)
            ->get()
            ->filter(function ($task) use ($windowStart, $windowEnd) {
                $dueDate = Carbon::parse($task->due_date);
                return $dueDate->between($windowStart, $windowEnd);
            });
    }

    /**
     * Envoie une notification de retard pour une tâche spécifique
     *
     * @param Task $task
     * @return bool True si envoyé, False si déjà notifié
     * @throws \Exception
     */
    public function sendOverdueNotification(Task $task): bool
    {
        // Vérification de sécurité
        if ($task->overdue_notification_sent) {
            Log::info("Notification déjà envoyée pour la tâche {$task->id}");
            return false;
        }

        if (!$this->isTaskOverdue($task)) {
            Log::warning("Tentative d'envoi de notification pour une tâche non en retard: {$task->id}");
            return false;
        }

        // Envoi de l'email
        Mail::to($task->user->email)->send(new TaskReminderMail($task));

        // Marquage comme notifié
        $task->update(['overdue_notification_sent' => true]);

        $overdueMinutes = $this->calculateOverdueMinutes($task);
        Log::info("Notification de retard envoyée pour la tâche {$task->id} ({$task->title}) - Retard: {$overdueMinutes} minutes");

        // Broadcast real-time overdue notification
        $this->realTimeNotificationService->broadcastTaskOverdue($task);

        return true;
    }

    /**
     * Vérifie si une tâche est effectivement en retard selon nos critères
     *
     * @param Task $task
     * @return bool
     */
    public function isTaskOverdue(Task $task): bool
    {
        if (!$task->due_date || $task->status === 'completed') {
            return false;
        }

        $now = Carbon::now();
        $dueDate = Carbon::parse($task->due_date);
        
        return $dueDate->addMinutes(self::OVERDUE_NOTIFICATION_DELAY_MINUTES)->isPast();
    }

    /**
     * Calcule le nombre de minutes de retard d'une tâche
     *
     * @param Task $task
     * @return int
     */
    public function calculateOverdueMinutes(Task $task): int
    {
        if (!$task->due_date) {
            return 0;
        }

        $now = Carbon::now();
        $dueDate = Carbon::parse($task->due_date);
        
        return max(0, $now->diffInMinutes($dueDate));
    }

    /**
     * Réinitialise le marqueur de notification pour une tâche
     * Utile pour les tests ou la maintenance
     *
     * @param Task $task
     * @return bool
     */
    public function resetNotificationFlag(Task $task): bool
    {
        return $task->update(['overdue_notification_sent' => false]);
    }

    /**
     * Obtient des statistiques sur les tâches en retard
     *
     * @return array
     */
    public function getOverdueStatistics(): array
    {
        $now = Carbon::now();
        
        return [
            'total_overdue_tasks' => Task::whereIn('status', ['to_do', 'in_progress'])
                ->whereNotNull('due_date')
                ->get()
                ->filter(function ($task) use ($now) {
                    return Carbon::parse($task->due_date)->isPast();
                })
                ->count(),

            'pending_notifications' => Task::whereIn('status', ['to_do', 'in_progress'])
                ->whereNotNull('due_date')
                ->where('overdue_notification_sent', false)
                ->get()
                ->filter(function ($task) {
                    return $this->isTaskOverdue($task);
                })
                ->count(),

            'already_notified' => Task::where('overdue_notification_sent', true)->count(),
        ];
    }
}
