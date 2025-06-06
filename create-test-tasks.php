<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use Carbon\Carbon;

echo "🎯 Création de tâches de test pour admin@test.com\n";
echo "================================================\n\n";

// Récupérer l'utilisateur admin@test.com
$adminUser = User::where('email', 'admin@test.com')->first();

if (!$adminUser) {
    echo "❌ Utilisateur admin@test.com non trouvé!\n";
    exit(1);
}

echo "✅ Utilisateur trouvé: {$adminUser->name} ({$adminUser->email})\n\n";

// Supprimer les anciennes tâches de test pour cet utilisateur
Task::where('user_id', $adminUser->id)->delete();
echo "🧹 Anciennes tâches supprimées\n\n";

echo "📝 Création des tâches de test...\n\n";

// 1. Tâches en retard (pour tester les notifications critiques)
$overdueTasks = [
    [
        'title' => '🚨 Tâche TRÈS en retard',
        'description' => 'Cette tâche est en retard de plus de 2 heures - doit déclencher une alerte critique',
        'due_date' => Carbon::now()->subHours(2)->subMinutes(15),
        'priority' => 'high',
        'status' => 'to_do'
    ],
    [
        'title' => '⚠️ Tâche en retard modéré',
        'description' => 'Cette tâche est en retard de 45 minutes - doit apparaître dans le badge rouge',
        'due_date' => Carbon::now()->subMinutes(45),
        'priority' => 'medium',
        'status' => 'in_progress'
    ],
    [
        'title' => '🔥 Tâche critique en retard',
        'description' => 'Tâche haute priorité en retard de 1 heure',
        'due_date' => Carbon::now()->subHour(),
        'priority' => 'high',
        'status' => 'to_do'
    ]
];

// 2. Tâches en attente (futures)
$pendingTasks = [
    [
        'title' => '📅 Tâche due dans 2 heures',
        'description' => 'Tâche à venir - doit apparaître dans la section "en attente"',
        'due_date' => Carbon::now()->addHours(2),
        'priority' => 'medium',
        'status' => 'to_do'
    ],
    [
        'title' => '🕐 Tâche due demain',
        'description' => 'Tâche programmée pour demain',
        'due_date' => Carbon::now()->addDay(),
        'priority' => 'low',
        'status' => 'to_do'
    ],
    [
        'title' => '📋 Tâche en cours',
        'description' => 'Tâche actuellement en progression',
        'due_date' => Carbon::now()->addHours(4),
        'priority' => 'high',
        'status' => 'in_progress'
    ]
];

// 3. Tâches sans date d'échéance
$noDueDateTasks = [
    [
        'title' => '📝 Tâche sans échéance',
        'description' => 'Tâche sans date limite - doit apparaître dans "en attente"',
        'due_date' => null,
        'priority' => 'low',
        'status' => 'to_do'
    ],
    [
        'title' => '🔄 Tâche récurrente',
        'description' => 'Tâche qui se répète régulièrement',
        'due_date' => null,
        'priority' => 'medium',
        'status' => 'in_progress'
    ]
];

// 4. Tâches complétées (pour les statistiques)
$completedTasks = [
    [
        'title' => '✅ Tâche terminée récemment',
        'description' => 'Tâche complétée pour tester les statistiques',
        'due_date' => Carbon::now()->subDay(),
        'priority' => 'medium',
        'status' => 'completed'
    ],
    [
        'title' => '🎉 Autre tâche terminée',
        'description' => 'Deuxième tâche complétée',
        'due_date' => Carbon::now()->subHours(3),
        'priority' => 'high',
        'status' => 'completed'
    ]
];

// Créer toutes les tâches
$allTasks = array_merge($overdueTasks, $pendingTasks, $noDueDateTasks, $completedTasks);
$createdCount = 0;

foreach ($allTasks as $taskData) {
    $task = Task::create([
        'user_id' => $adminUser->id,
        'title' => $taskData['title'],
        'description' => $taskData['description'],
        'due_date' => $taskData['due_date'],
        'priority' => $taskData['priority'],
        'status' => $taskData['status'],
        'completed_at' => $taskData['status'] === 'completed' ? Carbon::now() : null,
        'is_auto_generated' => false,
        'overdue_notification_sent' => false
    ]);
    
    $createdCount++;
    $dueDateStr = $task->due_date ? $task->due_date->format('d/m/Y H:i') : 'Aucune';
    echo "  ✓ {$task->title} (Échéance: {$dueDateStr}, Priorité: {$task->priority}, Statut: {$task->status})\n";
}

echo "\n🎉 {$createdCount} tâches de test créées avec succès!\n\n";

// Statistiques pour validation
$stats = [
    'total' => Task::where('user_id', $adminUser->id)->count(),
    'completed' => Task::where('user_id', $adminUser->id)->where('status', 'completed')->count(),
    'pending' => Task::where('user_id', $adminUser->id)->where('status', '!=', 'completed')->count(),
    'overdue' => Task::where('user_id', $adminUser->id)
        ->where('status', '!=', 'completed')
        ->whereNotNull('due_date')
        ->where('due_date', '<', Carbon::now())
        ->count(),
    'high_priority' => Task::where('user_id', $adminUser->id)
        ->where('status', '!=', 'completed')
        ->where('priority', 'high')
        ->count()
];

echo "📊 Statistiques des tâches créées:\n";
echo "  • Total: {$stats['total']}\n";
echo "  • Terminées: {$stats['completed']}\n";
echo "  • En attente: {$stats['pending']}\n";
echo "  • En retard: {$stats['overdue']}\n";
echo "  • Haute priorité: {$stats['high_priority']}\n\n";

echo "🚀 Prêt pour tester la Phase 3!\n";
echo "Connectez-vous avec: admin@test.com / password\n";
