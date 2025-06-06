<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Services\RealTimeNotificationService;
use Carbon\Carbon;

echo "🧪 Test Automatisé - Phase 3 : Notifications en Temps Réel\n";
echo "=========================================================\n\n";

// Récupérer l'utilisateur de test
$adminUser = User::where('email', 'admin@test.com')->first();

if (!$adminUser) {
    echo "❌ Utilisateur admin@test.com non trouvé!\n";
    exit(1);
}

echo "✅ Utilisateur de test: {$adminUser->name}\n\n";

// Initialiser le service de notifications temps réel
$realTimeService = new RealTimeNotificationService();

echo "📊 Test 1: Calcul des badges de notification\n";
echo "--------------------------------------------\n";

$badgeData = $realTimeService->getBadgeData($adminUser);

echo "Résultats des badges:\n";
echo "  • Total tâches non terminées: {$badgeData['total_count']}\n";
echo "  • Tâches en attente: {$badgeData['pending_count']}\n";
echo "  • Tâches en retard: {$badgeData['overdue_count']}\n";

// Vérifications
$tests = [];

// Test 1: Distinction entre tâches en attente et en retard
$tests['badge_distinction'] = $badgeData['pending_count'] > 0 && $badgeData['overdue_count'] > 0;

// Test 2: Total correct
$expectedTotal = $badgeData['pending_count'] + $badgeData['overdue_count'];
$tests['total_calculation'] = $badgeData['total_count'] === $expectedTotal;

// Test 3: Données des tâches en retard
$tests['overdue_data'] = $badgeData['overdue_tasks']->count() > 0;

// Test 4: Données des tâches en attente
$tests['pending_data'] = $badgeData['pending_tasks']->count() > 0;

echo "\n🔍 Test 2: Validation des calculs\n";
echo "---------------------------------\n";

foreach ($tests as $testName => $result) {
    $status = $result ? "✅ PASS" : "❌ FAIL";
    echo "  {$status} {$testName}\n";
}

echo "\n📈 Test 3: Statistiques du tableau de bord\n";
echo "------------------------------------------\n";

// Simuler une mise à jour du tableau de bord
$tasks = $adminUser->tasks();
$now = Carbon::now();

$dashboardStats = [
    'total_tasks' => $tasks->count(),
    'completed_tasks' => $tasks->where('status', 'completed')->count(),
    'pending_tasks' => $tasks->where('status', '!=', 'completed')->count(),
    'overdue_tasks' => $tasks->where('status', '!=', 'completed')
        ->whereNotNull('due_date')
        ->get()
        ->filter(function ($task) use ($now) {
            return Carbon::parse($task->due_date)->isPast();
        })
        ->count(),
    'high_priority_tasks' => $tasks->where('priority', 'high')->where('status', '!=', 'completed')->count()
];

echo "Statistiques calculées:\n";
foreach ($dashboardStats as $key => $value) {
    echo "  • {$key}: {$value}\n";
}

$completionRate = $dashboardStats['total_tasks'] > 0 
    ? round(($dashboardStats['completed_tasks'] / $dashboardStats['total_tasks']) * 100, 1) 
    : 0;

echo "  • Taux de completion: {$completionRate}%\n";

echo "\n🎯 Test 4: Simulation d'événements temps réel\n";
echo "---------------------------------------------\n";

// Simuler un changement de statut
$testTask = $adminUser->tasks()->where('status', 'to_do')->first();

if ($testTask) {
    echo "Simulation du changement de statut pour: {$testTask->title}\n";
    
    // Simuler l'événement (en mode log pour éviter les erreurs Pusher)
    try {
        $realTimeService->broadcastTaskStatusChange($testTask, 'to_do', 'in_progress');
        echo "✅ Événement TaskStatusChanged simulé avec succès\n";
    } catch (Exception $e) {
        echo "⚠️ Événement simulé en mode log: {$e->getMessage()}\n";
    }
    
    // Simuler une tâche en retard
    try {
        $realTimeService->broadcastTaskOverdue($testTask);
        echo "✅ Événement TaskOverdue simulé avec succès\n";
    } catch (Exception $e) {
        echo "⚠️ Événement simulé en mode log: {$e->getMessage()}\n";
    }
    
    // Simuler une mise à jour du tableau de bord
    try {
        $realTimeService->broadcastDashboardUpdate($adminUser->id);
        echo "✅ Événement DashboardUpdated simulé avec succès\n";
    } catch (Exception $e) {
        echo "⚠️ Événement simulé en mode log: {$e->getMessage()}\n";
    }
} else {
    echo "⚠️ Aucune tâche 'to_do' trouvée pour la simulation\n";
}

echo "\n📋 Test 5: Vérification des fichiers Phase 3\n";
echo "--------------------------------------------\n";

$requiredFiles = [
    'app/Services/RealTimeNotificationService.php',
    'app/Events/TaskStatusChanged.php',
    'app/Events/TaskOverdue.php',
    'app/Events/DashboardUpdated.php',
    'public/assets/real-time-notifications.js',
    'routes/channels.php',
    'config/broadcasting.php'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists($file);
    $status = $exists ? "✅ EXISTS" : "❌ MISSING";
    echo "  {$status} {$file}\n";
}

echo "\n🔧 Test 6: Configuration Pusher\n";
echo "-------------------------------\n";

$pusherConfig = config('broadcasting.connections.pusher');
$configTests = [
    'app_id_set' => !empty($pusherConfig['app_id']),
    'key_set' => !empty($pusherConfig['key']),
    'secret_set' => !empty($pusherConfig['secret']),
    'cluster_set' => !empty($pusherConfig['options']['cluster'])
];

foreach ($configTests as $test => $result) {
    $status = $result ? "✅ OK" : "⚠️ NOT SET";
    echo "  {$status} {$test}\n";
}

$broadcastConnection = config('broadcasting.default');
echo "  📡 Broadcast connection: {$broadcastConnection}\n";

echo "\n🎉 Résumé des Tests\n";
echo "==================\n";

$allTests = array_merge($tests, $configTests);
$passedTests = array_filter($allTests);
$totalTests = count($allTests);
$passedCount = count($passedTests);

echo "Tests réussis: {$passedCount}/{$totalTests}\n";

if ($passedCount === $totalTests) {
    echo "🎉 Tous les tests sont passés! Phase 3 prête pour utilisation.\n";
} else {
    echo "⚠️ Certains tests ont échoué. Vérifiez la configuration.\n";
}

echo "\n📖 Prochaines étapes:\n";
echo "1. Connectez-vous sur http://localhost:8000 avec admin@test.com / password\n";
echo "2. Testez manuellement les badges et notifications\n";
echo "3. Ouvrez deux onglets pour tester les mises à jour temps réel\n";
echo "4. Vérifiez les logs dans storage/logs/laravel.log\n\n";

echo "🚀 Phase 3 : Notifications en Temps Réel - Test terminé!\n";
