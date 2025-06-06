<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Note;
use App\Models\Routine;
use App\Models\Reminder;
use App\Services\DataEncryptionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Phase4SecurityTestDataSeeder extends Seeder
{
    protected DataEncryptionService $encryptionService;

    public function __construct()
    {
        $this->encryptionService = app(DataEncryptionService::class);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔒 Génération des données de test pour la Phase 4 : Sécurité Avancée...');

        // Créer 4 utilisateurs avec des profils différents
        $users = $this->createTestUsers();
        $this->command->info('✅ 4 utilisateurs créés avec données chiffrées');

        // Créer 10 tâches par utilisateur (40 total)
        $this->createTestTasks($users);
        $this->command->info('✅ 40 tâches créées avec titres et descriptions chiffrés');

        // Créer 5 notes par utilisateur (20 total)
        $this->createTestNotes($users);
        $this->command->info('✅ 20 notes créées avec contenu chiffré');

        // Créer 2 routines par utilisateur (8 total)
        $this->createTestRoutines($users);
        $this->command->info('✅ 8 routines créées avec données chiffrées');

        // Créer 2 rappels par utilisateur (8 total)
        $this->createTestReminders($users);
        $this->command->info('✅ 8 rappels créés avec données chiffrées');

        $this->command->info('🎉 Données de test Phase 4 générées avec succès !');
        $this->command->info('📊 Résumé : 4 utilisateurs, 40 tâches, 20 notes, 8 routines, 8 rappels');
    }

    /**
     * Créer 4 utilisateurs de test avec des profils différents
     */
    protected function createTestUsers(): array
    {
        $users = [];

        // Utilisateur 1 : Administrateur
        $users[] = User::create([
            'name' => 'Admin Sécurité',
            'email' => 'admin@sonama-it.com',
            'password' => Hash::make('SecureAdmin2024!'),
            'whatsapp_number' => '+229 97 12 34 56', // Sera chiffré automatiquement
            'email_verified_at' => now(),
        ]);

        // Utilisateur 2 : Manager
        $users[] = User::create([
            'name' => 'Marie Dupont',
            'email' => 'marie.dupont@sonama-it.com',
            'password' => Hash::make('Manager2024!'),
            'whatsapp_number' => '+229 96 78 90 12', // Sera chiffré automatiquement
            'email_verified_at' => now(),
        ]);

        // Utilisateur 3 : Développeur
        $users[] = User::create([
            'name' => 'Jean Martin',
            'email' => 'jean.martin@sonama-it.com',
            'password' => Hash::make('Developer2024!'),
            'whatsapp_number' => '+229 95 45 67 89', // Sera chiffré automatiquement
            'email_verified_at' => now(),
        ]);

        // Utilisateur 4 : Testeur
        $users[] = User::create([
            'name' => 'Sophie Tester',
            'email' => 'sophie.test@sonama-it.com',
            'password' => Hash::make('Tester2024!'),
            'whatsapp_number' => '+229 94 11 22 33', // Sera chiffré automatiquement
            'email_verified_at' => now(),
        ]);

        return $users;
    }

    /**
     * Créer 10 tâches par utilisateur avec données sensibles
     */
    protected function createTestTasks(array $users): void
    {
        $taskTemplates = [
            [
                'title' => 'Audit de sécurité mensuel',
                'description' => 'Effectuer l\'audit complet des systèmes de sécurité incluant les logs, les accès et les vulnérabilités. Données confidentielles : serveurs critiques 192.168.1.100-110',
                'priority' => 'high'
            ],
            [
                'title' => 'Mise à jour des certificats SSL',
                'description' => 'Renouveler les certificats SSL pour tous les domaines. Clés privées stockées dans /etc/ssl/private/. Contact fournisseur : cert@provider.com',
                'priority' => 'high'
            ],
            [
                'title' => 'Formation sécurité équipe',
                'description' => 'Organiser une session de formation sur les bonnes pratiques de sécurité. Budget alloué : 5000€. Formateur : SecureTraining SARL',
                'priority' => 'medium'
            ],
            [
                'title' => 'Backup des données clients',
                'description' => 'Effectuer la sauvegarde hebdomadaire des données clients sensibles. Serveur de backup : backup.internal.com (IP: 10.0.0.50)',
                'priority' => 'high'
            ],
            [
                'title' => 'Test de pénétration',
                'description' => 'Planifier et exécuter des tests de pénétration sur l\'infrastructure. Prestataire : PenTest Pro (contact@pentest-pro.fr)',
                'priority' => 'medium'
            ],
            [
                'title' => 'Révision des accès utilisateurs',
                'description' => 'Réviser tous les accès utilisateurs et supprimer les comptes inactifs. Base LDAP : ldap.company.local:389',
                'priority' => 'medium'
            ],
            [
                'title' => 'Mise à jour pare-feu',
                'description' => 'Appliquer les dernières règles de sécurité sur le pare-feu principal. IP firewall : 192.168.1.1 - Login admin requis',
                'priority' => 'high'
            ],
            [
                'title' => 'Chiffrement base de données',
                'description' => 'Implémenter le chiffrement TDE sur la base de données principale. Serveur DB : db-prod-01.internal (Port 5432)',
                'priority' => 'high'
            ],
            [
                'title' => 'Monitoring des logs',
                'description' => 'Configurer les alertes de sécurité dans le SIEM. Serveur SIEM : siem.company.com - API Key: sk_live_abc123xyz',
                'priority' => 'medium'
            ],
            [
                'title' => 'Documentation sécurité',
                'description' => 'Mettre à jour la documentation des procédures de sécurité. Inclure les nouveaux processus de chiffrement AES-256-GCM',
                'priority' => 'low'
            ]
        ];

        foreach ($users as $user) {
            foreach ($taskTemplates as $index => $template) {
                Task::create([
                    'user_id' => $user->id,
                    'title' => $template['title'] . " - {$user->name}",
                    'description' => $template['description'],
                    'priority' => $template['priority'],
                    'status' => $this->getRandomStatus(),
                    'due_date' => $this->getRandomDueDate(),
                    'assigned_to' => $index % 2 === 0 ? $user->id : null,
                    'is_auto_generated' => false,
                    'overdue_notification_sent' => false,
                ]);
            }
        }
    }

    /**
     * Créer 5 notes par utilisateur avec contenu sensible
     */
    protected function createTestNotes(array $users): void
    {
        $noteTemplates = [
            [
                'title' => 'Mots de passe serveurs',
                'content' => 'Serveur Web: admin/WebSecure2024! | DB Server: dbadmin/DbPass2024! | Backup: backup/BackupKey2024!'
            ],
            [
                'title' => 'Contacts urgence sécurité',
                'content' => 'CERT: +33 1 23 45 67 89 | Police cyber: +33 1 98 76 54 32 | Assurance cyber: cyber@assurance.fr'
            ],
            [
                'title' => 'Clés API sensibles',
                'content' => 'AWS: AKIA1234567890ABCDEF | Azure: abc123-def456-ghi789 | Google: AIzaSyABC123DEF456GHI789'
            ],
            [
                'title' => 'Procédure incident sécurité',
                'content' => '1. Isoler le système compromis 2. Contacter RSSI: rssi@company.com 3. Documenter dans ticket #SEC-2024'
            ],
            [
                'title' => 'Configuration VPN',
                'content' => 'Serveur VPN: vpn.company.com:1194 | Certificat client: client.ovpn | Passphrase: VpnSecure2024!'
            ]
        ];

        foreach ($users as $user) {
            foreach ($noteTemplates as $template) {
                Note::create([
                    'user_id' => $user->id,
                    'title' => $template['title'] . " - {$user->name}",
                    'content' => $template['content'],
                    'date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                ]);
            }
        }
    }

    /**
     * Créer 2 routines par utilisateur
     */
    protected function createTestRoutines(array $users): void
    {
        $routineTemplates = [
            [
                'title' => 'Vérification quotidienne sécurité',
                'description' => 'Contrôle quotidien des logs de sécurité, des tentatives d\'intrusion et des alertes système. Vérifier dashboard SIEM à 9h00.',
                'frequency' => 'daily'
            ],
            [
                'title' => 'Rapport hebdomadaire sécurité',
                'description' => 'Génération du rapport hebdomadaire incluant les incidents, les mises à jour et les métriques de sécurité. Envoi à direction@company.com',
                'frequency' => 'weekly'
            ]
        ];

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
            }
        }
    }

    /**
     * Créer 2 rappels par utilisateur
     */
    protected function createTestReminders(array $users): void
    {
        $reminderTemplates = [
            [
                'title' => 'Renouvellement certificat SSL',
                'description' => 'Le certificat SSL expire dans 30 jours. Contacter le fournisseur pour le renouvellement. Coût estimé: 500€/an'
            ],
            [
                'title' => 'Audit de conformité RGPD',
                'description' => 'Audit trimestriel de conformité RGPD prévu. Préparer la documentation des traitements et des mesures de sécurité.'
            ]
        ];

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
            }
        }
    }

    /**
     * Obtenir un statut aléatoire pour les tâches
     */
    protected function getRandomStatus(): string
    {
        $statuses = ['to_do', 'in_progress', 'completed'];
        return $statuses[array_rand($statuses)];
    }

    /**
     * Obtenir une date d'échéance aléatoire
     */
    protected function getRandomDueDate(): Carbon
    {
        return now()->addDays(rand(-10, 30));
    }
}
