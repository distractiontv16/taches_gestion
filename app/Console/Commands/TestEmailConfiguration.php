<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Task;
use App\Mail\TaskReminderMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestEmailConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email-config {--user-id=2} {--send-test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test de la configuration email Mailtrap et envoi d\'email de test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== TEST DE CONFIGURATION EMAIL MAILTRAP ===");
        $this->info("");

        // 1. Vérifier la configuration
        $this->displayEmailConfiguration();

        // 2. Vérifier l'utilisateur
        $userId = $this->option('user-id');
        $user = $this->verifyUser($userId);

        if (!$user) {
            return 1;
        }

        // 3. Test d'envoi si demandé
        if ($this->option('send-test')) {
            $this->sendTestEmail($user);
        } else {
            $this->info("💡 Utilisez --send-test pour envoyer un email de test");
        }

        return 0;
    }

    /**
     * Affiche la configuration email actuelle
     */
    private function displayEmailConfiguration(): void
    {
        $this->info("📧 CONFIGURATION EMAIL ACTUELLE:");

        $config = [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password') ? '***masqué***' : 'NON DÉFINI',
            'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME' => config('mail.from.name'),
        ];

        foreach ($config as $key => $value) {
            $status = $value ? '✅' : '❌';
            $this->info("   {$status} {$key}: {$value}");
        }

        // Vérification spécifique Mailtrap
        $this->info("");
        if (config('mail.mailers.smtp.host') === 'sandbox.smtp.mailtrap.io') {
            $this->info("✅ Configuration Mailtrap détectée");
        } else {
            $this->error("❌ Configuration Mailtrap non détectée");
        }
    }

    /**
     * Vérifie l'utilisateur
     */
    private function verifyUser(int $userId): ?User
    {
        $this->info("");
        $this->info("👤 VÉRIFICATION UTILISATEUR (ID: {$userId}):");

        $user = User::find($userId);

        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");

            // Afficher les utilisateurs disponibles
            $this->info("📋 Utilisateurs disponibles:");
            $users = User::select('id', 'name', 'email')->get();

            $tableData = [];
            foreach ($users as $u) {
                $tableData[] = [$u->id, $u->name, $u->email];
            }

            $this->table(['ID', 'Nom', 'Email'], $tableData);
            return null;
        }

        $this->info("✅ Utilisateur trouvé:");
        $this->info("   • ID: {$user->id}");
        $this->info("   • Nom: {$user->name}");
        $this->info("   • Email: {$user->email}");

        return $user;
    }

    /**
     * Envoie un email de test
     */
    private function sendTestEmail(User $user): void
    {
        $this->info("");
        $this->info("📧 ENVOI D'EMAIL DE TEST:");

        try {
            // Créer une tâche de test temporaire
            $testTask = new Task([
                'id' => 999,
                'title' => 'TEST EMAIL - Tâche de validation système',
                'description' => 'Email de test pour vérifier la configuration Mailtrap',
                'due_date' => Carbon::now()->subMinutes(35),
                'priority' => 'high',
                'status' => 'to_do',
                'is_auto_generated' => false,
                'overdue_notification_sent' => false,
            ]);

            // Simuler la relation user
            $testTask->setRelation('user', $user);

            $this->info("🚀 Envoi en cours vers: {$user->email}");

            // Envoyer l'email
            Mail::to($user->email)->send(new TaskReminderMail($testTask));

            $this->info("✅ Email envoyé avec succès!");
            $this->info("📬 Vérifiez votre boîte Mailtrap: https://mailtrap.io/inboxes");

            Log::info("Test email envoyé avec succès à {$user->email}");

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'envoi:");
            $this->error("   Message: " . $e->getMessage());
            $this->error("   Fichier: " . $e->getFile() . ":" . $e->getLine());

            Log::error("Erreur test email: " . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'trace' => $e->getTraceAsString()
            ]);

            // Suggestions de résolution
            $this->info("");
            $this->info("🔧 SUGGESTIONS DE RÉSOLUTION:");
            $this->info("1. Vérifiez les identifiants Mailtrap dans .env");
            $this->info("2. Vérifiez que le service Mailtrap est actif");
            $this->info("3. Vérifiez les logs: storage/logs/laravel.log");
        }
    }
}
