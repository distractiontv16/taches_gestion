<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskOverdueNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestOverdueNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-overdue-notifications {--create-test-task} {--reset-flags} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test et validation du système de notifications de tâches en retard';

    /**
     * Service de notifications
     *
     * @var TaskOverdueNotificationService
     */
    protected $overdueService;

    /**
     * Constructeur
     */
    public function __construct(TaskOverdueNotificationService $overdueService)
    {
        parent::__construct();
        $this->overdueService = $overdueService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== TEST DU SYSTÈME DE NOTIFICATIONS DE RETARD ===");
        $this->info("Spécification: Notifications 30 minutes APRÈS l'échéance");
        $this->info("");

        // Options de test
        if ($this->option('create-test-task')) {
            $this->createTestTask();
            return;
        }

        if ($this->option('reset-flags')) {
            $this->resetNotificationFlags();
            return;
        }

        // Test principal
        $this->runTests();
    }

    /**
     * Exécute les tests principaux
     */
    private function runTests(): void
    {
        $this->info("1. 📊 STATISTIQUES ACTUELLES");
        $this->displayCurrentStatistics();

        $this->info("");
        $this->info("2. 🔍 TÂCHES ÉLIGIBLES POUR NOTIFICATION");
        $this->displayEligibleTasks();

        $this->info("");
        $this->info("3. 🧪 TEST DE LA LOGIQUE DE DÉTECTION");
        $this->testDetectionLogic();

        if (!$this->option('dry-run')) {
            $this->info("");
            $this->info("4. 📧 SIMULATION D'ENVOI (mode réel)");
            $this->simulateNotificationSending();
        } else {
            $this->info("");
            $this->info("4. 📧 MODE DRY-RUN (aucun email envoyé)");
        }
    }

    /**
     * Affiche les statistiques actuelles
     */
    private function displayCurrentStatistics(): void
    {
        $stats = $this->overdueService->getOverdueStatistics();

        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Total tâches en retard', $stats['total_overdue_tasks']],
                ['En attente de notification', $stats['pending_notifications']],
                ['Déjà notifiées', $stats['already_notified']],
            ]
        );
    }

    /**
     * Affiche les tâches éligibles
     */
    private function displayEligibleTasks(): void
    {
        $eligibleTasks = $this->overdueService->findEligibleOverdueTasks();

        if ($eligibleTasks->isEmpty()) {
            $this->info("✅ Aucune tâche éligible pour notification actuellement");
            return;
        }

        $this->info("🎯 {$eligibleTasks->count()} tâche(s) éligible(s) trouvée(s):");

        $tableData = [];
        foreach ($eligibleTasks as $task) {
            $overdueMinutes = $this->overdueService->calculateOverdueMinutes($task);
            $tableData[] = [
                $task->id,
                $task->title,
                $task->due_date->format('d/m/Y H:i'),
                "{$overdueMinutes} min",
                $task->user->name,
                $task->is_auto_generated ? 'Auto' : 'Manuel'
            ];
        }

        $this->table(
            ['ID', 'Titre', 'Échéance', 'Retard', 'Utilisateur', 'Type'],
            $tableData
        );
    }

    /**
     * Teste la logique de détection
     */
    private function testDetectionLogic(): void
    {
        $now = Carbon::now();
        $this->info("⏰ Heure actuelle: {$now->format('Y-m-d H:i:s')}");

        // Test avec différents scénarios
        $testCases = [
            ['description' => 'Tâche due il y a exactement 30 minutes', 'minutes_ago' => 30],
            ['description' => 'Tâche due il y a 25 minutes (trop tôt)', 'minutes_ago' => 25],
            ['description' => 'Tâche due il y a 35 minutes (dans la fenêtre)', 'minutes_ago' => 35],
            ['description' => 'Tâche due il y a 40 minutes (trop tard)', 'minutes_ago' => 40],
        ];

        foreach ($testCases as $case) {
            $testDueDate = $now->copy()->subMinutes($case['minutes_ago']);
            $testTask = new Task([
                'due_date' => $testDueDate,
                'status' => 'to_do',
                'overdue_notification_sent' => false
            ]);

            $isOverdue = $this->overdueService->isTaskOverdue($testTask);
            $icon = $isOverdue ? '✅' : '❌';

            $this->info("{$icon} {$case['description']}: " . ($isOverdue ? 'ÉLIGIBLE' : 'NON ÉLIGIBLE'));
        }
    }

    /**
     * Simule l'envoi de notifications
     */
    private function simulateNotificationSending(): void
    {
        if ($this->option('dry-run')) {
            $this->info("Mode dry-run activé - aucun email ne sera envoyé");
            return;
        }

        $this->info("🚀 Lancement du processus de notification...");

        $stats = $this->overdueService->processOverdueTasks();

        $this->info("📊 Résultats:");
        $this->info("   • Tâches traitées: {$stats['processed']}");
        $this->info("   • Notifications envoyées: {$stats['sent']}");
        $this->info("   • Déjà notifiées: {$stats['already_notified']}");
        $this->info("   • Erreurs: {$stats['errors']}");
    }

    /**
     * Crée une tâche de test
     */
    private function createTestTask(): void
    {
        $this->info("🧪 Création d'une tâche de test...");

        // Utiliser l'utilisateur ID 2 comme demandé
        $user = User::find(2);
        if (!$user) {
            $this->error("Utilisateur avec ID 2 non trouvé dans la base de données");
            return;
        }

        $dueDate = Carbon::now()->subMinutes(30); // Exactement 30 minutes dans le passé

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'TEST - Tâche en retard pour validation',
            'description' => 'Tâche créée automatiquement pour tester le système de notifications',
            'due_date' => $dueDate,
            'priority' => 'high',
            'status' => 'to_do',
            'overdue_notification_sent' => false,
            'is_auto_generated' => false,
        ]);

        $this->info("✅ Tâche de test créée:");
        $this->info("   • ID: {$task->id}");
        $this->info("   • Titre: {$task->title}");
        $this->info("   • Échéance: {$dueDate->format('Y-m-d H:i:s')}");
        $this->info("   • Retard: 30 minutes (éligible pour notification)");
    }

    /**
     * Remet à zéro les flags de notification
     */
    private function resetNotificationFlags(): void
    {
        $this->info("🔄 Remise à zéro des flags de notification...");

        $count = Task::where('overdue_notification_sent', true)->count();
        Task::where('overdue_notification_sent', true)->update(['overdue_notification_sent' => false]);

        $this->info("✅ {$count} tâche(s) réinitialisée(s)");
    }
}
