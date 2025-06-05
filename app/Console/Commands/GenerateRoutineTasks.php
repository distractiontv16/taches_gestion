<?php

namespace App\Console\Commands;

use App\Services\RoutineTaskGeneratorService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateRoutineTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-routine-tasks 
                            {--date= : Date spécifique pour générer les tâches (format: Y-m-d)}
                            {--days-ahead=1 : Nombre de jours à l\'avance pour générer les tâches}
                            {--preview : Afficher un aperçu sans générer les tâches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère automatiquement les tâches à partir des routines actives';

    protected RoutineTaskGeneratorService $generatorService;

    public function __construct(RoutineTaskGeneratorService $generatorService)
    {
        parent::__construct();
        $this->generatorService = $generatorService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('🚀 Début de la génération des tâches routinières...');
            Log::info('Commande de génération des tâches routinières lancée');

            // Déterminer la date de génération
            $targetDate = $this->getTargetDate();
            $daysAhead = (int) $this->option('days-ahead');
            $isPreview = $this->option('preview');

            $this->info("📅 Date cible: {$targetDate->format('Y-m-d')} ({$targetDate->format('l')})");
            
            if ($daysAhead > 1) {
                $endDate = $targetDate->copy()->addDays($daysAhead - 1);
                $this->info("📅 Période: du {$targetDate->format('Y-m-d')} au {$endDate->format('Y-m-d')}");
            }

            if ($isPreview) {
                return $this->handlePreview($targetDate, $daysAhead);
            }

            // Génération effective
            if ($daysAhead === 1) {
                $results = $this->generatorService->generateTasksForDate($targetDate);
                $this->displaySingleDayResults($results, $targetDate);
            } else {
                $endDate = $targetDate->copy()->addDays($daysAhead - 1);
                $results = $this->generatorService->generateTasksForDateRange($targetDate, $endDate);
                $this->displayMultipleDaysResults($results);
            }

            $this->info('✅ Génération terminée avec succès!');
            Log::info('Commande de génération des tâches routinières terminée avec succès');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la génération: ' . $e->getMessage());
            Log::error('Erreur lors de la génération des tâches routinières: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return 1;
        }
    }

    /**
     * Détermine la date cible pour la génération
     */
    private function getTargetDate(): Carbon
    {
        $dateOption = $this->option('date');
        
        if ($dateOption) {
            try {
                return Carbon::createFromFormat('Y-m-d', $dateOption);
            } catch (\Exception $e) {
                $this->error("❌ Format de date invalide: {$dateOption}. Utilisation de la date d'aujourd'hui.");
                return now();
            }
        }

        return now();
    }

    /**
     * Gère l'aperçu des tâches qui seraient générées
     */
    private function handlePreview(Carbon $targetDate, int $daysAhead): int
    {
        $this->info('👁️  Mode aperçu - Aucune tâche ne sera générée');
        $this->newLine();

        // Récupérer toutes les routines actives
        $routines = \App\Models\Routine::active()->with('user')->get();

        if ($routines->isEmpty()) {
            $this->warn('⚠️  Aucune routine active trouvée');
            return 0;
        }

        $this->info("📋 {$routines->count()} routine(s) active(s) trouvée(s)");
        $this->newLine();

        $totalPreviewTasks = 0;

        foreach ($routines as $routine) {
            $preview = $this->generatorService->previewTasksForRoutine($routine, $daysAhead);
            
            if (!empty($preview)) {
                $this->info("🔄 Routine: {$routine->title} (Utilisateur: {$routine->user->name})");
                
                $headers = ['Date', 'Jour', 'Heure d\'échéance', 'Priorité'];
                $rows = [];
                
                foreach ($preview as $task) {
                    $rows[] = [
                        $task['date'],
                        $task['day_name'],
                        Carbon::parse($task['due_datetime'])->format('H:i'),
                        ucfirst($task['priority'])
                    ];
                    $totalPreviewTasks++;
                }
                
                $this->table($headers, $rows);
                $this->newLine();
            }
        }

        $this->info("📊 Total des tâches qui seraient générées: {$totalPreviewTasks}");
        
        return 0;
    }

    /**
     * Affiche les résultats pour une seule journée
     */
    private function displaySingleDayResults(array $results, Carbon $date): void
    {
        $this->newLine();
        $this->info("📊 Résultats pour {$date->format('Y-m-d')}:");
        $this->info("   • Routines traitées: {$results['total_routines_processed']}");
        $this->info("   • Tâches générées: {$results['tasks_generated']}");

        if (!empty($results['generated_tasks'])) {
            $this->newLine();
            $this->info('✨ Tâches générées:');
            
            foreach ($results['generated_tasks'] as $task) {
                $this->line("   → {$task['task_title']} (ID: {$task['task_id']})");
            }
        }

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('❌ Erreurs rencontrées:');
            foreach ($results['errors'] as $error) {
                $this->error("   • {$error}");
            }
        }
    }

    /**
     * Affiche les résultats pour plusieurs jours
     */
    private function displayMultipleDaysResults(array $results): void
    {
        $this->newLine();
        $this->info("📊 Résultats globaux:");
        $this->info("   • Jours traités: {$results['total_days_processed']}");
        $this->info("   • Total tâches générées: {$results['total_tasks_generated']}");

        if (!empty($results['daily_results'])) {
            $this->newLine();
            $this->info('📅 Détail par jour:');
            
            foreach ($results['daily_results'] as $date => $dayResults) {
                $this->line("   {$date}: {$dayResults['tasks_generated']} tâche(s) générée(s)");
            }
        }

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('❌ Erreurs rencontrées:');
            foreach ($results['errors'] as $error) {
                $this->error("   • {$error}");
            }
        }
    }
}
