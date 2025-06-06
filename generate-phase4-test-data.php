<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Models\Note;
use App\Models\Routine;
use App\Models\Reminder;
use App\Services\DataEncryptionService;
use App\Services\SecurityAuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

echo "🔒 GÉNÉRATION DES DONNÉES DE TEST - PHASE 4 : SÉCURITÉ AVANCÉE\n";
echo "============================================================\n\n";

// Vérifier que les services de sécurité sont disponibles
try {
    $encryptionService = app(DataEncryptionService::class);
    $auditService = app(SecurityAuditService::class);
    echo "✅ Services de sécurité initialisés\n";
} catch (Exception $e) {
    echo "❌ Erreur d'initialisation des services: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n📋 NETTOYAGE DES DONNÉES EXISTANTES\n";
echo "-----------------------------------\n";

// Nettoyer les données existantes (optionnel)
$response = readline("Voulez-vous supprimer toutes les données existantes ? (y/N): ");
if (strtolower($response) === 'y') {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    Reminder::truncate();
    echo "🗑️  Rappels supprimés\n";
    
    Task::truncate();
    echo "🗑️  Tâches supprimées\n";
    
    Note::truncate();
    echo "🗑️  Notes supprimées\n";
    
    Routine::truncate();
    echo "🗑️  Routines supprimées\n";
    
    User::truncate();
    echo "🗑️  Utilisateurs supprimés\n";
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "✅ Nettoyage terminé\n";
}

echo "\n👥 CRÉATION DES UTILISATEURS DE TEST\n";
echo "------------------------------------\n";

// Créer 4 utilisateurs avec des profils différents
$users = [];

// Utilisateur 1 : Administrateur
$users[] = User::create([
    'name' => 'Admin Sécurité',
    'email' => 'admin@sonama-it.com',
    'password' => Hash::make('SecureAdmin2024!'),
    'whatsapp_number' => '+229 97 12 34 56',
    'email_verified_at' => now(),
]);
echo "✅ Utilisateur Admin créé (WhatsApp chiffré)\n";

// Utilisateur 2 : Manager
$users[] = User::create([
    'name' => 'Marie Dupont',
    'email' => 'marie.dupont@sonama-it.com',
    'password' => Hash::make('Manager2024!'),
    'whatsapp_number' => '+229 96 78 90 12',
    'email_verified_at' => now(),
]);
echo "✅ Utilisateur Manager créé (WhatsApp chiffré)\n";

// Utilisateur 3 : Développeur
$users[] = User::create([
    'name' => 'Jean Martin',
    'email' => 'jean.martin@sonama-it.com',
    'password' => Hash::make('Developer2024!'),
    'whatsapp_number' => '+229 95 45 67 89',
    'email_verified_at' => now(),
]);
echo "✅ Utilisateur Développeur créé (WhatsApp chiffré)\n";

// Utilisateur 4 : Testeur
$users[] = User::create([
    'name' => 'Sophie Tester',
    'email' => 'sophie.test@sonama-it.com',
    'password' => Hash::make('Tester2024!'),
    'whatsapp_number' => '+229 94 11 22 33',
    'email_verified_at' => now(),
]);
echo "✅ Utilisateur Testeur créé (WhatsApp chiffré)\n";

echo "\n📝 CRÉATION DES TÂCHES AVEC DONNÉES SENSIBLES\n";
echo "---------------------------------------------\n";

$taskTemplates = [
    [
        'title' => 'Audit de sécurité mensuel',
        'description' => 'Effectuer l\'audit complet des systèmes incluant les serveurs critiques 192.168.1.100-110 et les clés API sensibles.',
        'priority' => 'high'
    ],
    [
        'title' => 'Mise à jour certificats SSL',
        'description' => 'Renouveler certificats pour domaines critiques. Clés privées dans /etc/ssl/private/. Contact: cert@provider.com',
        'priority' => 'high'
    ],
    [
        'title' => 'Formation sécurité équipe',
        'description' => 'Session formation bonnes pratiques. Budget: 5000€. Formateur: SecureTraining SARL (contact@securetraining.fr)',
        'priority' => 'medium'
    ],
    [
        'title' => 'Backup données clients',
        'description' => 'Sauvegarde hebdomadaire données sensibles. Serveur: backup.internal.com (IP: 10.0.0.50, Login: backup_admin)',
        'priority' => 'high'
    ],
    [
        'title' => 'Test de pénétration',
        'description' => 'Tests pénétration infrastructure. Prestataire: PenTest Pro (contact@pentest-pro.fr, Tarif: 150€/jour)',
        'priority' => 'medium'
    ],
    [
        'title' => 'Révision accès utilisateurs',
        'description' => 'Révision complète accès et suppression comptes inactifs. LDAP: ldap.company.local:389 (admin/LdapPass2024!)',
        'priority' => 'medium'
    ],
    [
        'title' => 'Mise à jour pare-feu',
        'description' => 'Application règles sécurité pare-feu principal. IP: 192.168.1.1 (admin/FirewallSecure2024!)',
        'priority' => 'high'
    ],
    [
        'title' => 'Chiffrement base de données',
        'description' => 'Implémentation TDE sur DB principale. Serveur: db-prod-01.internal:5432 (postgres/DbSecure2024!)',
        'priority' => 'high'
    ],
    [
        'title' => 'Monitoring des logs SIEM',
        'description' => 'Configuration alertes sécurité. Serveur: siem.company.com (API Key: sk_live_abc123xyz456)',
        'priority' => 'medium'
    ],
    [
        'title' => 'Documentation procédures',
        'description' => 'MAJ documentation sécurité incluant processus chiffrement AES-256-GCM et clés de rotation.',
        'priority' => 'low'
    ]
];

$taskCount = 0;
foreach ($users as $user) {
    foreach ($taskTemplates as $template) {
        $taskData = [
            'user_id' => $user->id,
            'title' => $template['title'] . " - {$user->name}",
            'description' => $template['description'],
            'priority' => $template['priority'],
            'status' => ['to_do', 'in_progress', 'completed'][array_rand(['to_do', 'in_progress', 'completed'])],
            'due_date' => now()->addDays(rand(-10, 30)),
        ];

        // Ajouter les colonnes optionnelles si elles existent (SANS project_id)
        if (Schema::hasColumn('tasks', 'assigned_to')) {
            $taskData['assigned_to'] = $user->id;
        }
        if (Schema::hasColumn('tasks', 'is_auto_generated')) {
            $taskData['is_auto_generated'] = false;
        }
        if (Schema::hasColumn('tasks', 'overdue_notification_sent')) {
            $taskData['overdue_notification_sent'] = false;
        }
        if (Schema::hasColumn('tasks', 'target_date')) {
            $taskData['target_date'] = now()->addDays(rand(1, 15))->format('Y-m-d');
        }

        Task::create($taskData);
        $taskCount++;
    }
}
echo "✅ {$taskCount} tâches créées avec titres et descriptions chiffrés\n";

echo "\n📄 CRÉATION DES NOTES AVEC CONTENU SENSIBLE\n";
echo "-------------------------------------------\n";

$noteTemplates = [
    [
        'title' => 'Mots de passe serveurs critiques',
        'content' => 'Web Server: admin/WebSecure2024! | DB Server: dbadmin/DbPass2024! | Backup: backup/BackupKey2024! | Mail: mail/MailSecure2024!'
    ],
    [
        'title' => 'Contacts urgence sécurité',
        'content' => 'CERT National: +33 1 23 45 67 89 | Police Cyber: +33 1 98 76 54 32 | Assurance Cyber: cyber@assurance.fr | RSSI: rssi@company.com'
    ],
    [
        'title' => 'Clés API et tokens sensibles',
        'content' => 'AWS Access: AKIA1234567890ABCDEF | Azure Client: abc123-def456-ghi789 | Google API: AIzaSyABC123DEF456GHI789 | Stripe: sk_live_xyz789'
    ],
    [
        'title' => 'Procédure incident sécurité',
        'content' => '1. Isoler système compromis 2. Contacter RSSI: rssi@company.com 3. Documenter ticket #SEC-2024 4. Notifier CNIL si RGPD 5. Communication interne'
    ],
    [
        'title' => 'Configuration VPN et accès distants',
        'content' => 'VPN Principal: vpn.company.com:1194 | Certificat: client.ovpn | Passphrase: VpnSecure2024! | Backup VPN: vpn2.company.com:443'
    ]
];

$noteCount = 0;
foreach ($users as $user) {
    foreach ($noteTemplates as $template) {
        Note::create([
            'user_id' => $user->id,
            'title' => $template['title'] . " - {$user->name}",
            'content' => $template['content'],
            'date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
        ]);
        $noteCount++;
    }
}
echo "✅ {$noteCount} notes créées avec contenu chiffré\n";

echo "\n🔄 CRÉATION DES ROUTINES DE SÉCURITÉ\n";
echo "------------------------------------\n";

$routineTemplates = [
    [
        'title' => 'Vérification quotidienne sécurité',
        'description' => 'Contrôle quotidien logs sécurité, tentatives intrusion, alertes système. Dashboard SIEM à 9h00. Rapport à security@company.com',
        'frequency' => 'daily'
    ],
    [
        'title' => 'Rapport hebdomadaire sécurité',
        'description' => 'Génération rapport incluant incidents, mises à jour, métriques. Envoi direction@company.com et board@company.com',
        'frequency' => 'weekly'
    ]
];

$routineCount = 0;
foreach ($users as $user) {
    foreach ($routineTemplates as $template) {
        Routine::create([
            'user_id' => $user->id,
            'title' => $template['title'] . " - {$user->name}",
            'description' => $template['description'],
            'frequency' => $template['frequency'],
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'due_time' => '16:00:00',
            'workdays_only' => true,
            'is_active' => true,
            'priority' => 'high',
            'total_tasks_generated' => 0,
        ]);
        $routineCount++;
    }
}
echo "✅ {$routineCount} routines créées avec descriptions chiffrées\n";

echo "\n⏰ CRÉATION DES RAPPELS SÉCURISÉS\n";
echo "--------------------------------\n";

$reminderTemplates = [
    [
        'title' => 'Renouvellement certificat SSL critique',
        'description' => 'Certificat SSL expire dans 30 jours. Fournisseur: CertProvider SA (contact@certprovider.com). Coût: 500€/an. Domaines: *.company.com'
    ],
    [
        'title' => 'Audit conformité RGPD trimestriel',
        'description' => 'Audit RGPD prévu. Préparer documentation traitements, mesures sécurité, registre. Contact DPO: dpo@company.com'
    ]
];

$reminderCount = 0;
foreach ($users as $user) {
    foreach ($reminderTemplates as $template) {
        Reminder::create([
            'user_id' => $user->id,
            'title' => $template['title'] . " - {$user->name}",
            'description' => $template['description'],
            'date' => now()->addDays(rand(1, 30))->format('Y-m-d'),
            'time' => '10:00:00',
            'email_sent' => false,
        ]);
        $reminderCount++;
    }
}
echo "✅ {$reminderCount} rappels créés avec descriptions chiffrées\n";

echo "\n🔍 VALIDATION DU CHIFFREMENT\n";
echo "----------------------------\n";

// Vérifier que le chiffrement fonctionne
$testUser = $users[0];
$testTask = Task::where('user_id', $testUser->id)->first();

echo "Test de validation du chiffrement:\n";
echo "- Utilisateur WhatsApp (chiffré): " . (strlen($testUser->whatsapp_number) > 20 ? "✅ Chiffré" : "❌ Non chiffré") . "\n";
echo "- Tâche titre (chiffré): " . (strlen($testTask->title) > 50 ? "✅ Chiffré" : "❌ Non chiffré") . "\n";
echo "- Tâche description (chiffrée): " . (strlen($testTask->description) > 100 ? "✅ Chiffrée" : "❌ Non chiffrée") . "\n";

echo "\n📊 RÉSUMÉ DES DONNÉES GÉNÉRÉES\n";
echo "==============================\n";
echo "👥 Utilisateurs: " . count($users) . " (avec numéros WhatsApp chiffrés)\n";
echo "📝 Tâches: {$taskCount} (titres et descriptions chiffrés)\n";
echo "📄 Notes: {$noteCount} (titres et contenus chiffrés)\n";
echo "🔄 Routines: {$routineCount} (titres et descriptions chiffrés)\n";
echo "⏰ Rappels: {$reminderCount} (titres et descriptions chiffrés)\n";

echo "\n🔐 INFORMATIONS DE CONNEXION\n";
echo "============================\n";
echo "Admin: admin@sonama-it.com / SecureAdmin2024!\n";
echo "Manager: marie.dupont@sonama-it.com / Manager2024!\n";
echo "Développeur: jean.martin@sonama-it.com / Developer2024!\n";
echo "Testeur: sophie.test@sonama-it.com / Tester2024!\n";

echo "\n✅ GÉNÉRATION TERMINÉE AVEC SUCCÈS!\n";
echo "===================================\n";
echo "🎯 Vous pouvez maintenant tester:\n";
echo "   - Le chiffrement/déchiffrement dans l'interface\n";
echo "   - Les fonctionnalités CSRF et headers de sécurité\n";
echo "   - L'audit de sécurité en temps réel\n";
echo "   - Les logs de sécurité dans storage/logs/security.log\n";
echo "\n🚀 Accédez à l'application et connectez-vous avec un des comptes ci-dessus!\n";
