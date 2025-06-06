<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use Carbon\Carbon;

echo "📧 CRÉATION DE TÂCHES SPÉCIFIQUES POUR TEST EMAIL\n";
echo "===============================================\n\n";

// Récupérer l'utilisateur admin@test.com
$adminUser = User::where('email', 'admin@test.com')->first();

if (!$adminUser) {
    echo "❌ Utilisateur admin@test.com non trouvé!\n";
    exit(1);
}

echo "✅ Utilisateur: {$adminUser->name} ({$adminUser->email})\n\n";

// Supprimer les anciennes tâches de test email
Task::where('user_id', $adminUser->id)
    ->where('title', 'like', '%TEST EMAIL%')
    ->delete();

echo "🧹 Anciennes tâches de test email supprimées\n\n";

$now = Carbon::now();

// Créer des tâches avec différents niveaux de retard pour tester le timing
$testTasks = [
    [
        'title' => '📧 TEST EMAIL - Exactement 30min de retard',
        'description' => 'Tâche en retard de exactement 30 minutes - doit déclencher notification',
        'due_date' => $now->copy()->subMinutes(30),
        'priority' => 'high',
        'should_trigger' => true
    ],
    [
        'title' => '📧 TEST EMAIL - 35min de retard',
        'description' => 'Tâche en retard de 35 minutes - doit déclencher notification',
        'due_date' => $now->copy()->subMinutes(35),
        'priority' => 'medium',
        'should_trigger' => true
    ],
    [
        'title' => '📧 TEST EMAIL - 45min de retard',
        'description' => 'Tâche en retard de 45 minutes - doit déclencher notification',
        'due_date' => $now->copy()->subMinutes(45),
        'priority' => 'high',
        'should_trigger' => true
    ],
    [
        'title' => '📧 TEST EMAIL - 25min de retard',
        'description' => 'Tâche en retard de seulement 25 minutes - NE DOIT PAS déclencher notification',
        'due_date' => $now->copy()->subMinutes(25),
        'priority' => 'low',
        'should_trigger' => false
    ],
    [
        'title' => '📧 TEST EMAIL - 15min de retard',
        'description' => 'Tâche en retard de 15 minutes - NE DOIT PAS déclencher notification',
        'due_date' => $now->copy()->subMinutes(15),
        'priority' => 'medium',
        'should_trigger' => false
    ],
    [
        'title' => '📧 TEST EMAIL - Future (dans 1h)',
        'description' => 'Tâche future - NE DOIT PAS déclencher notification',
        'due_date' => $now->copy()->addHour(),
        'priority' => 'low',
        'should_trigger' => false
    ]
];

echo "📝 Création des tâches de test...\n\n";

foreach ($testTasks as $taskData) {
    $task = Task::create([
        'user_id' => $adminUser->id,
        'title' => $taskData['title'],
        'description' => $taskData['description'],
        'due_date' => $taskData['due_date'],
        'priority' => $taskData['priority'],
        'status' => 'to_do',
        'is_auto_generated' => false,
        'overdue_notification_sent' => false
    ]);
    
    $minutesOverdue = $now->diffInMinutes($taskData['due_date'], false);
    $triggerStatus = $taskData['should_trigger'] ? "✅ DOIT déclencher" : "❌ NE DOIT PAS déclencher";

    // Correction de l'affichage du retard
    $retardDisplay = $minutesOverdue > 0 ? "{$minutesOverdue} minutes de retard" : "dans " . abs($minutesOverdue) . " minutes";

    echo "  ✓ {$task->title}\n";
    echo "    Échéance: {$taskData['due_date']->format('d/m/Y H:i:s')}\n";
    echo "    Statut: {$retardDisplay}\n";
    echo "    {$triggerStatus}\n\n";
}

echo "🎯 INSTRUCTIONS DE TEST\n";
echo "======================\n\n";

echo "1. 📧 Testez l'envoi direct avec:\n";
echo "   php test-email-system.php\n\n";

echo "2. ⚡ Testez la commande complète avec:\n";
echo "   php artisan app:send-reminder-emails\n\n";

echo "3. 🔧 Testez la configuration avec:\n";
echo "   php artisan app:test-email-config --send-test\n\n";

echo "4. 📊 Vérifiez les résultats attendus:\n";
echo "   • 3 emails DOIVENT être envoyés (30min, 35min, 45min de retard)\n";
echo "   • 3 tâches NE DOIVENT PAS déclencher d'email (25min, 15min, future)\n\n";

echo "5. 📬 Vérifiez votre boîte Mailtrap:\n";
echo "   https://mailtrap.io/inboxes\n\n";

echo "6. 📋 Vérifiez les logs:\n";
echo "   Get-Content storage/logs/laravel.log -Tail 20\n\n";

// Afficher un résumé des tâches créées
$totalTasks = Task::where('user_id', $adminUser->id)->where('title', 'like', '%TEST EMAIL%')->count();
$eligibleTasks = Task::where('user_id', $adminUser->id)
    ->where('title', 'like', '%TEST EMAIL%')
    ->where('due_date', '<=', $now->copy()->subMinutes(30))
    ->where('overdue_notification_sent', false)
    ->count();

echo "📊 RÉSUMÉ\n";
echo "========\n";
echo "• Tâches de test créées: {$totalTasks}\n";
echo "• Tâches éligibles pour notification: {$eligibleTasks}\n";
echo "• Utilisateur de test: {$adminUser->email}\n\n";

echo "🚀 Prêt pour les tests d'emails Phase 2!\n";
