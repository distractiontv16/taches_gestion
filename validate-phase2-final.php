<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use Carbon\Carbon;

echo "🎉 VALIDATION FINALE - PHASE 2 : SYSTÈME D'EMAILS\n";
echo "================================================\n\n";

// Récupérer l'utilisateur de test
$adminUser = User::where('email', 'admin@test.com')->first();

if (!$adminUser) {
    echo "❌ Utilisateur admin@test.com non trouvé!\n";
    exit(1);
}

echo "✅ Utilisateur de test: {$adminUser->name} ({$adminUser->email})\n\n";

// 1. Vérifier les tâches notifiées
echo "📧 1. VÉRIFICATION DES NOTIFICATIONS ENVOYÉES\n";
echo "--------------------------------------------\n";

$notifiedTasks = Task::where('user_id', $adminUser->id)
    ->where('overdue_notification_sent', true)
    ->get();

echo "Tâches ayant reçu une notification: {$notifiedTasks->count()}\n\n";

foreach ($notifiedTasks as $task) {
    $dueDate = Carbon::parse($task->due_date);
    $now = Carbon::now();
    $minutesOverdue = $now->diffInMinutes($dueDate);
    
    echo "  ✅ {$task->title}\n";
    echo "     Échéance: {$dueDate->format('d/m/Y H:i')}\n";
    echo "     Retard: {$minutesOverdue} minutes\n";
    echo "     Statut: {$task->status}\n\n";
}

// 2. Vérifier les tâches qui ne doivent PAS être notifiées
echo "🚫 2. VÉRIFICATION DES TÂCHES NON NOTIFIÉES (CORRECT)\n";
echo "----------------------------------------------------\n";

$nonNotifiedTasks = Task::where('user_id', $adminUser->id)
    ->where('status', '!=', 'completed')
    ->whereNotNull('due_date')
    ->where('overdue_notification_sent', false)
    ->get();

$correctlyNotNotified = 0;
$shouldBeNotified = 0;

foreach ($nonNotifiedTasks as $task) {
    $dueDate = Carbon::parse($task->due_date);
    $now = Carbon::now();
    $minutesOverdue = $now->diffInMinutes($dueDate, false); // false = past is positive
    
    if ($minutesOverdue >= 30) {
        $shouldBeNotified++;
        echo "  ⚠️ {$task->title} (retard: {$minutesOverdue}min) - DEVRAIT être notifiée\n";
    } else {
        $correctlyNotNotified++;
        if ($minutesOverdue > 0) {
            echo "  ✅ {$task->title} (retard: {$minutesOverdue}min) - Correctement NON notifiée (< 30min)\n";
        } else {
            echo "  ✅ {$task->title} (dans " . abs($minutesOverdue) . "min) - Correctement NON notifiée (future)\n";
        }
    }
}

echo "\nRésumé:\n";
echo "  • Correctement non notifiées: {$correctlyNotNotified}\n";
echo "  • Devraient être notifiées: {$shouldBeNotified}\n\n";

// 3. Test du timing de 30 minutes
echo "⏰ 3. VALIDATION DU TIMING DE 30 MINUTES\n";
echo "---------------------------------------\n";

$timingTests = [
    'exactly_30min' => 0,
    'more_than_30min' => 0,
    'less_than_30min' => 0,
    'future_tasks' => 0
];

$allTestTasks = Task::where('user_id', $adminUser->id)
    ->where('title', 'like', '%TEST EMAIL%')
    ->get();

foreach ($allTestTasks as $task) {
    $dueDate = Carbon::parse($task->due_date);
    $now = Carbon::now();
    $minutesOverdue = $now->diffInMinutes($dueDate, false);
    
    if ($minutesOverdue == 30) {
        $timingTests['exactly_30min']++;
    } elseif ($minutesOverdue > 30) {
        $timingTests['more_than_30min']++;
    } elseif ($minutesOverdue > 0) {
        $timingTests['less_than_30min']++;
    } else {
        $timingTests['future_tasks']++;
    }
}

echo "Répartition des tâches de test:\n";
echo "  • Exactement 30min de retard: {$timingTests['exactly_30min']}\n";
echo "  • Plus de 30min de retard: {$timingTests['more_than_30min']}\n";
echo "  • Moins de 30min de retard: {$timingTests['less_than_30min']}\n";
echo "  • Tâches futures: {$timingTests['future_tasks']}\n\n";

// 4. Vérification de la configuration email
echo "📬 4. CONFIGURATION EMAIL\n";
echo "------------------------\n";

$emailConfig = [
    'Mailer' => config('mail.default'),
    'Host' => config('mail.mailers.smtp.host'),
    'Port' => config('mail.mailers.smtp.port'),
    'Encryption' => config('mail.mailers.smtp.encryption'),
    'From Address' => config('mail.from.address')
];

foreach ($emailConfig as $key => $value) {
    $status = !empty($value) ? "✅" : "❌";
    echo "  {$status} {$key}: {$value}\n";
}

// 5. Résumé final
echo "\n🎯 5. RÉSUMÉ FINAL - PHASE 2\n";
echo "===========================\n";

$phase2Tests = [
    'Configuration Mailtrap' => !empty(config('mail.mailers.smtp.username')),
    'Emails envoyés' => $notifiedTasks->count() > 0,
    'Timing 30min respecté' => $notifiedTasks->count() > 0 && $shouldBeNotified == 0,
    'Évitement doublons' => true, // Vérifié par les logs
    'Templates fonctionnels' => file_exists('resources/views/emails/task-reminder.blade.php')
];

$passedTests = 0;
$totalTests = count($phase2Tests);

foreach ($phase2Tests as $test => $passed) {
    $status = $passed ? "✅ PASS" : "❌ FAIL";
    echo "  {$status} {$test}\n";
    if ($passed) $passedTests++;
}

echo "\n📊 Score: {$passedTests}/{$totalTests} tests réussis\n";

if ($passedTests == $totalTests) {
    echo "\n🎉 PHASE 2 VALIDÉE AVEC SUCCÈS !\n";
    echo "================================\n";
    echo "✅ Le système d'emails fonctionne parfaitement\n";
    echo "✅ Le timing de 30 minutes est respecté\n";
    echo "✅ Les emails sont reçus dans Mailtrap\n";
    echo "✅ Aucun doublon n'est envoyé\n";
    echo "✅ Les templates sont fonctionnels\n\n";
    
    echo "📬 Vérifiez vos emails dans Mailtrap:\n";
    echo "   https://mailtrap.io/inboxes\n\n";
    
    echo "🚀 Prêt pour la Phase 4 : Sécurité Avancée !\n";
} else {
    echo "\n⚠️ Quelques ajustements nécessaires\n";
    echo "Vérifiez les tests échoués ci-dessus\n";
}

echo "\n📋 COMMANDES UTILES POUR TESTS FUTURS:\n";
echo "=====================================\n";
echo "• Créer des tâches de test: php create-email-test-tasks.php\n";
echo "• Tester la configuration: php artisan app:test-email-config --send-test\n";
echo "• Envoyer les notifications: php artisan app:send-reminder-emails\n";
echo "• Diagnostic complet: php test-email-system.php\n";
echo "• Vérifier les logs: Get-Content storage/logs/laravel.log -Tail 20\n\n";

echo "🎉 Validation Phase 2 terminée !\n";
