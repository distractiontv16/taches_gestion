<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Mail\TaskReminderMail;
use App\Services\TaskOverdueNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

echo "🔧 DIAGNOSTIC COMPLET DU SYSTÈME D'EMAILS - PHASE 2\n";
echo "==================================================\n\n";

// 1. Vérification de la configuration
echo "📋 1. VÉRIFICATION DE LA CONFIGURATION MAILTRAP\n";
echo "-----------------------------------------------\n";

$mailConfig = [
    'mailer' => config('mail.default'),
    'host' => config('mail.mailers.smtp.host'),
    'port' => config('mail.mailers.smtp.port'),
    'username' => config('mail.mailers.smtp.username'),
    'password' => config('mail.mailers.smtp.password'),
    'encryption' => config('mail.mailers.smtp.encryption'),
    'from_address' => config('mail.from.address'),
    'from_name' => config('mail.from.name')
];

foreach ($mailConfig as $key => $value) {
    $displayValue = in_array($key, ['password']) ? str_repeat('*', strlen($value)) : $value;
    $status = !empty($value) ? "✅" : "❌";
    echo "  {$status} {$key}: {$displayValue}\n";
}

// 2. Test de connexion SMTP
echo "\n🌐 2. TEST DE CONNEXION SMTP\n";
echo "----------------------------\n";

try {
    $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
        config('mail.mailers.smtp.host'),
        config('mail.mailers.smtp.port'),
        config('mail.mailers.smtp.encryption') === 'tls'
    );
    
    $transport->setUsername(config('mail.mailers.smtp.username'));
    $transport->setPassword(config('mail.mailers.smtp.password'));
    
    echo "✅ Configuration SMTP valide\n";
} catch (Exception $e) {
    echo "❌ Erreur de configuration SMTP: " . $e->getMessage() . "\n";
}

// 3. Vérification de l'utilisateur de test
echo "\n👤 3. VÉRIFICATION DE L'UTILISATEUR DE TEST\n";
echo "------------------------------------------\n";

$adminUser = User::where('email', 'admin@test.com')->first();

if (!$adminUser) {
    echo "❌ Utilisateur admin@test.com non trouvé!\n";
    exit(1);
}

echo "✅ Utilisateur trouvé: {$adminUser->name} ({$adminUser->email})\n";

// 4. Analyse des tâches éligibles pour notification
echo "\n📊 4. ANALYSE DES TÂCHES ÉLIGIBLES\n";
echo "---------------------------------\n";

$now = Carbon::now();
$tasks = $adminUser->tasks()->where('status', '!=', 'completed')->whereNotNull('due_date')->get();

echo "Total tâches non terminées avec échéance: {$tasks->count()}\n\n";

$categories = [
    'overdue_eligible' => [],
    'overdue_not_eligible' => [],
    'upcoming' => [],
    'already_notified' => []
];

foreach ($tasks as $task) {
    $dueDate = Carbon::parse($task->due_date);
    $minutesOverdue = $now->diffInMinutes($dueDate, false); // false = past is positive
    
    if ($task->overdue_notification_sent) {
        $categories['already_notified'][] = $task;
    } elseif ($minutesOverdue >= 30) {
        $categories['overdue_eligible'][] = $task;
    } elseif ($minutesOverdue > 0) {
        $categories['overdue_not_eligible'][] = $task;
    } else {
        $categories['upcoming'][] = $task;
    }
}

foreach ($categories as $category => $taskList) {
    $count = count($taskList);
    echo "📋 " . ucfirst(str_replace('_', ' ', $category)) . ": {$count}\n";
    
    foreach ($taskList as $task) {
        $dueDate = Carbon::parse($task->due_date);
        $minutesOverdue = $now->diffInMinutes($dueDate, false);
        $status = $minutesOverdue > 0 ? "en retard de {$minutesOverdue}min" : "dans " . abs($minutesOverdue) . "min";
        echo "  • {$task->title} ({$status})\n";
    }
    echo "\n";
}

// 5. Test d'envoi d'email direct
echo "📧 5. TEST D'ENVOI D'EMAIL DIRECT\n";
echo "--------------------------------\n";

if (!empty($categories['overdue_eligible'])) {
    $testTask = $categories['overdue_eligible'][0];
    echo "Test avec la tâche: {$testTask->title}\n";
    
    try {
        echo "🚀 Envoi en cours vers {$adminUser->email}...\n";
        
        // Envoyer l'email
        Mail::to($adminUser->email)->send(new TaskReminderMail($testTask));
        
        echo "✅ Email envoyé avec succès!\n";
        echo "📬 Vérifiez votre boîte Mailtrap: https://mailtrap.io/inboxes\n";
        
        // Log de l'envoi
        Log::info("Test email envoyé avec succès", [
            'task_id' => $testTask->id,
            'task_title' => $testTask->title,
            'user_email' => $adminUser->email,
            'test_mode' => true
        ]);
        
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'envoi: " . $e->getMessage() . "\n";
        echo "📁 Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
        
        Log::error("Erreur test email direct", [
            'error' => $e->getMessage(),
            'task_id' => $testTask->id,
            'user_email' => $adminUser->email
        ]);
    }
} else {
    echo "⚠️ Aucune tâche éligible pour test direct\n";
    echo "💡 Créons une tâche de test...\n";
    
    // Créer une tâche de test en retard
    $testTask = Task::create([
        'user_id' => $adminUser->id,
        'title' => '🧪 TEST EMAIL - Tâche en retard',
        'description' => 'Tâche créée automatiquement pour tester le système d\'emails',
        'due_date' => Carbon::now()->subMinutes(35),
        'priority' => 'high',
        'status' => 'to_do',
        'is_auto_generated' => false,
        'overdue_notification_sent' => false
    ]);
    
    echo "✅ Tâche de test créée: {$testTask->title}\n";
    
    try {
        echo "🚀 Envoi en cours vers {$adminUser->email}...\n";
        Mail::to($adminUser->email)->send(new TaskReminderMail($testTask));
        echo "✅ Email de test envoyé avec succès!\n";
        echo "📬 Vérifiez votre boîte Mailtrap: https://mailtrap.io/inboxes\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'envoi: " . $e->getMessage() . "\n";
    }
}

// 6. Test du service TaskOverdueNotificationService
echo "\n🔧 6. TEST DU SERVICE TASKOVERDUE\n";
echo "--------------------------------\n";

try {
    $overdueService = new TaskOverdueNotificationService(new \App\Services\RealTimeNotificationService());
    
    echo "🔍 Recherche des tâches éligibles...\n";
    $eligibleTasks = $overdueService->findEligibleOverdueTasks();
    echo "Tâches éligibles trouvées: {$eligibleTasks->count()}\n";
    
    foreach ($eligibleTasks as $task) {
        $overdueMinutes = $overdueService->calculateOverdueMinutes($task);
        echo "  • {$task->title} (retard: {$overdueMinutes}min)\n";
    }
    
    if ($eligibleTasks->count() > 0) {
        echo "\n🚀 Test du processus complet...\n";
        $stats = $overdueService->processOverdueTasks();
        
        echo "📊 Résultats:\n";
        echo "  • Traitées: {$stats['processed']}\n";
        echo "  • Envoyées: {$stats['sent']}\n";
        echo "  • Erreurs: {$stats['errors']}\n";
        echo "  • Déjà notifiées: {$stats['already_notified']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur dans le service: " . $e->getMessage() . "\n";
}

// 7. Test de la commande SendReminderEmails
echo "\n⚡ 7. TEST DE LA COMMANDE SENDREMINDEREMAILS\n";
echo "-------------------------------------------\n";

echo "💡 Pour tester la commande complète, exécutez:\n";
echo "   php artisan app:send-reminder-emails\n\n";

echo "💡 Pour tester la configuration email, exécutez:\n";
echo "   php artisan app:test-email-config --send-test\n\n";

// 8. Vérification des logs
echo "📋 8. VÉRIFICATION DES LOGS RÉCENTS\n";
echo "----------------------------------\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -10); // 10 dernières lignes
    
    echo "📄 10 dernières entrées du log:\n";
    foreach ($recentLogs as $log) {
        if (strpos($log, 'email') !== false || strpos($log, 'mail') !== false || strpos($log, 'Task') !== false) {
            echo "  " . trim($log) . "\n";
        }
    }
} else {
    echo "⚠️ Fichier de log non trouvé\n";
}

echo "\n🎯 RÉSUMÉ ET RECOMMANDATIONS\n";
echo "===========================\n";

$recommendations = [];

if (empty(config('mail.mailers.smtp.username'))) {
    $recommendations[] = "❌ Configurez les identifiants Mailtrap dans .env";
}

if (empty($categories['overdue_eligible']) && empty($categories['overdue_not_eligible'])) {
    $recommendations[] = "💡 Créez des tâches avec échéances dépassées pour tester";
}

if (count($recommendations) > 0) {
    echo "📋 Actions recommandées:\n";
    foreach ($recommendations as $rec) {
        echo "  {$rec}\n";
    }
} else {
    echo "✅ Configuration semble correcte!\n";
    echo "📬 Vérifiez votre boîte Mailtrap pour les emails de test\n";
}

echo "\n🔗 Liens utiles:\n";
echo "  • Mailtrap: https://mailtrap.io/inboxes\n";
echo "  • Logs Laravel: storage/logs/laravel.log\n";
echo "  • Configuration: .env (section MAIL_*)\n";

echo "\n🎉 Diagnostic terminé!\n";
