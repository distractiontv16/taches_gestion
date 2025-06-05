<?php

namespace App\Services;

use App\Models\Routine;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoutineTaskGeneratorService
{
    /**
     * Génère toutes les tâches pour une date donnée
     */
    public function generateTasksForDate(Carbon $date): array
    {
        $results = [
            'total_routines_processed' => 0,
            'tasks_generated' => 0,
            'errors' => [],
            'generated_tasks' => []
        ];

        try {
            // Récupérer toutes les routines actives
            $activeRoutines = Routine::active()->with('user')->get();
            $results['total_routines_processed'] = $activeRoutines->count();

            Log::info("Début de génération des tâches pour {$date->format('Y-m-d')}", [
                'total_routines' => $activeRoutines->count()
            ]);

            foreach ($activeRoutines as $routine) {
                try {
                    $task = $this->generateTaskForRoutine($routine, $date);
                    if ($task) {
                        $results['tasks_generated']++;
                        $results['generated_tasks'][] = [
                            'routine_id' => $routine->id,
                            'routine_title' => $routine->title,
                            'task_id' => $task->id,
                            'task_title' => $task->title,
                            'user_id' => $routine->user_id
                        ];
                    }
                } catch (\Exception $e) {
                    $error = "Erreur lors de la génération pour la routine {$routine->id}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    Log::error($error, [
                        'routine_id' => $routine->id,
                        'date' => $date->format('Y-m-d'),
                        'exception' => $e
                    ]);
                }
            }

            Log::info("Génération terminée pour {$date->format('Y-m-d')}", $results);

        } catch (\Exception $e) {
            $error = "Erreur générale lors de la génération: " . $e->getMessage();
            $results['errors'][] = $error;
            Log::error($error, ['exception' => $e]);
        }

        return $results;
    }

    /**
     * Génère une tâche pour une routine spécifique et une date donnée
     */
    public function generateTaskForRoutine(Routine $routine, Carbon $date): ?Task
    {
        // Vérifier si la routine doit générer une tâche pour cette date
        if (!$routine->shouldGenerateForDate($date)) {
            return null;
        }

        // Vérifier s'il n'y a pas déjà une tâche générée pour cette routine et cette date
        if ($this->taskAlreadyExists($routine, $date)) {
            Log::info("Tâche déjà existante pour la routine {$routine->id} et la date {$date->format('Y-m-d')}");
            return null;
        }

        return DB::transaction(function () use ($routine, $date) {
            // Calculer la date/heure d'échéance
            $dueDateTime = $routine->calculateDueDateTime($date);

            // Créer la tâche
            $task = Task::create([
                'user_id' => $routine->user_id,
                'routine_id' => $routine->id,
                'title' => $routine->title,
                'description' => $routine->description,
                'due_date' => $dueDateTime,
                'priority' => $routine->priority,
                'status' => 'to_do',
                'is_auto_generated' => true,
                'generation_date' => now()->toDateString(),
                'target_date' => $date->toDateString(),
            ]);

            // Marquer la routine comme ayant généré une tâche
            $routine->markAsGenerated($date);

            // Créer un rappel automatique pour la tâche
            $this->createTaskReminder($task);

            Log::info("Tâche générée avec succès", [
                'task_id' => $task->id,
                'routine_id' => $routine->id,
                'target_date' => $date->format('Y-m-d'),
                'due_date' => $dueDateTime->format('Y-m-d H:i:s')
            ]);

            return $task;
        });
    }

    /**
     * Vérifie si une tâche existe déjà pour une routine et une date
     */
    private function taskAlreadyExists(Routine $routine, Carbon $date): bool
    {
        return Task::fromRoutine($routine->id)
            ->forTargetDate($date)
            ->exists();
    }

    /**
     * Crée un rappel automatique pour une tâche générée
     */
    private function createTaskReminder(Task $task): void
    {
        if (!$task->due_date) {
            return;
        }

        try {
            // Créer un rappel 2 heures avant l'échéance
            $reminderTime = $task->due_date->copy()->subHours(2);

            $task->reminders()->create([
                'user_id' => $task->user_id,
                'title' => 'Rappel: ' . $task->title,
                'description' => 'Rappel automatique pour la tâche générée: ' . $task->title,
                'date' => $reminderTime->format('Y-m-d'),
                'time' => $reminderTime->format('H:i'),
                'email_sent' => false,
            ]);

            Log::info("Rappel créé pour la tâche {$task->id}");

        } catch (\Exception $e) {
            Log::error("Erreur lors de la création du rappel pour la tâche {$task->id}: " . $e->getMessage());
        }
    }

    /**
     * Génère les tâches pour plusieurs jours à venir
     */
    public function generateTasksForDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $results = [
            'total_days_processed' => 0,
            'total_tasks_generated' => 0,
            'daily_results' => [],
            'errors' => []
        ];

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dailyResults = $this->generateTasksForDate($currentDate);
            $results['daily_results'][$currentDate->format('Y-m-d')] = $dailyResults;
            $results['total_tasks_generated'] += $dailyResults['tasks_generated'];
            $results['total_days_processed']++;

            if (!empty($dailyResults['errors'])) {
                $results['errors'] = array_merge($results['errors'], $dailyResults['errors']);
            }

            $currentDate->addDay();
        }

        return $results;
    }

    /**
     * Génère les tâches pour aujourd'hui
     */
    public function generateTasksForToday(): array
    {
        return $this->generateTasksForDate(now());
    }

    /**
     * Génère les tâches pour demain (utile pour la planification)
     */
    public function generateTasksForTomorrow(): array
    {
        return $this->generateTasksForDate(now()->addDay());
    }

    /**
     * Obtient un aperçu des tâches qui seraient générées pour une routine
     */
    public function previewTasksForRoutine(Routine $routine, int $daysAhead = 7): array
    {
        $preview = [];
        $startDate = now();

        for ($i = 0; $i < $daysAhead; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            if ($routine->shouldGenerateForDate($date)) {
                $dueDateTime = $routine->calculateDueDateTime($date);
                
                $preview[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $date->format('l'),
                    'due_datetime' => $dueDateTime->format('Y-m-d H:i:s'),
                    'title' => $routine->title,
                    'priority' => $routine->priority
                ];
            }
        }

        return $preview;
    }
}
