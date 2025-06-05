<?php

namespace App\Console\Commands;

use App\Mail\ReminderMail;
use App\Models\Reminder;
use App\Services\TaskOverdueNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reminder-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails for upcoming reminders and overdue tasks (30 minutes AFTER due date)';

    /**
     * Service de notifications de tâches en retard
     *
     * @var TaskOverdueNotificationService
     */
    protected $overdueNotificationService;

    /**
     * Constructeur - Injection du service de notifications
     */
    public function __construct(TaskOverdueNotificationService $overdueNotificationService)
    {
        parent::__construct();
        $this->overdueNotificationService = $overdueNotificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info("=== Commande d'envoi d'emails lancée ===");
            Log::info("SendReminderEmails: Commande lancée");

            // 1. Traiter les rappels préventifs (système existant)
            $reminderStats = $this->sendReminderEmails();

            // 2. Traiter les notifications de tâches en retard (nouveau système corrigé)
            $overdueStats = $this->sendOverdueTaskNotifications();

            // 3. Afficher le résumé
            $this->displaySummary($reminderStats, $overdueStats);

            $this->info("=== Commande terminée avec succès ===");
            Log::info("SendReminderEmails: Commande terminée avec succès");

            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi des emails: ' . $e->getMessage());
            Log::error('SendReminderEmails: Erreur critique - ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return 1;
        }
    }
    
    /**
     * Traite les rappels préventifs (système existant)
     * Ces rappels sont créés 2 heures AVANT l'échéance et envoyés au moment prévu
     *
     * @return array Statistiques d'envoi
     */
    private function sendReminderEmails(): array
    {
        $this->info("--- RAPPELS PRÉVENTIFS ---");

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $thirtyMinutesAgo = Carbon::now()->subMinutes(30);

        $this->info("Recherche des rappels préventifs pour aujourd'hui: $today");
        $this->info("Fenêtre d'envoi: {$thirtyMinutesAgo->format('H:i')} à {$now->format('H:i')}");

        // Récupérer les rappels d'aujourd'hui non encore envoyés
        $reminders = Reminder::whereDate('date', $today)
            ->where('email_sent', false)
            ->get();

        $stats = [
            'processed' => $reminders->count(),
            'sent' => 0,
            'errors' => 0,
            'out_of_window' => 0
        ];
        
        foreach ($reminders as $reminder) {
            try {
                // Parse date et heure correctement
                $reminderDate = Carbon::parse($reminder->date);

                // Extraire seulement l'heure de time et l'appliquer à la date
                if (!empty($reminder->time)) {
                    // Si time contient seulement HH:MM
                    if (preg_match('/^\d{2}:\d{2}$/', $reminder->time)) {
                        $timeParts = explode(':', $reminder->time);
                        $reminderDate->setTime($timeParts[0], $timeParts[1], 0);
                    } else {
                        // Si time est déjà un objet Carbon ou une string complexe, on parse
                        $timeObj = Carbon::parse($reminder->time);
                        $reminderDate->setTime($timeObj->hour, $timeObj->minute, $timeObj->second);
                    }
                }

                // Vérifier si le rappel est dans la fenêtre d'envoi (30 dernières minutes)
                if ($reminderDate->isBetween($thirtyMinutesAgo, $now)) {
                    $this->info("📧 Envoi rappel préventif: {$reminder->title}");

                    try {
                        // Envoyer l'email
                        Mail::to($reminder->user->email)->send(new ReminderMail($reminder));

                        // Marquer comme envoyé
                        $reminder->email_sent = true;
                        $reminder->save();

                        $stats['sent']++;

                        $this->info("✅ Email envoyé: {$reminder->title} → {$reminder->user->email}");
                        Log::info("Rappel préventif envoyé: {$reminder->title} (ID: {$reminder->id})");

                    } catch (\Exception $e) {
                        $stats['errors']++;
                        $this->error("❌ Erreur envoi email: {$reminder->title} - " . $e->getMessage());
                        Log::error("Erreur envoi rappel préventif {$reminder->id}: " . $e->getMessage());
                    }
                } else {
                    $stats['out_of_window']++;
                    $this->info("⏰ Rappel hors fenêtre: {$reminder->title} ({$reminderDate->format('H:i')})");
                }

            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("❌ Erreur traitement rappel {$reminder->id}: " . $e->getMessage());
                Log::error("Erreur traitement rappel {$reminder->id}: " . $e->getMessage());
            }
        }

        $this->info("📊 Rappels préventifs: {$stats['sent']} envoyés / {$stats['processed']} traités");
        return $stats;
    }
    
    /**
     * Traite les notifications de tâches en retard (nouveau système corrigé)
     * Notifications envoyées exactement 30 minutes APRÈS l'échéance
     *
     * @return array Statistiques d'envoi
     */
    private function sendOverdueTaskNotifications(): array
    {
        $this->info("--- NOTIFICATIONS TÂCHES EN RETARD ---");
        $this->info("🎯 Critère: 30 minutes APRÈS l'échéance (spécification SoNaMA IT)");

        try {
            // Utiliser le service dédié pour traiter les tâches en retard
            $stats = $this->overdueNotificationService->processOverdueTasks();

            // Afficher les résultats détaillés
            if ($stats['sent'] > 0) {
                $this->info("🚨 {$stats['sent']} notifications de retard envoyées");
            }

            if ($stats['already_notified'] > 0) {
                $this->info("ℹ️  {$stats['already_notified']} tâches déjà notifiées (évitement doublons)");
            }

            if ($stats['errors'] > 0) {
                $this->error("❌ {$stats['errors']} erreurs lors de l'envoi");
            }

            // Afficher les statistiques générales
            $generalStats = $this->overdueNotificationService->getOverdueStatistics();
            $this->info("📊 Statistiques générales:");
            $this->info("   • Total tâches en retard: {$generalStats['total_overdue_tasks']}");
            $this->info("   • En attente de notification: {$generalStats['pending_notifications']}");
            $this->info("   • Déjà notifiées: {$generalStats['already_notified']}");

            return $stats;

        } catch (\Exception $e) {
            $this->error("❌ Erreur critique dans le traitement des tâches en retard: " . $e->getMessage());
            Log::error("SendReminderEmails: Erreur dans sendOverdueTaskNotifications - " . $e->getMessage());

            return [
                'processed' => 0,
                'sent' => 0,
                'errors' => 1,
                'already_notified' => 0
            ];
        }
    }

    /**
     * Affiche un résumé des opérations effectuées
     *
     * @param array $reminderStats
     * @param array $overdueStats
     */
    private function displaySummary(array $reminderStats, array $overdueStats): void
    {
        $this->info("");
        $this->info("=== RÉSUMÉ DES OPÉRATIONS ===");

        $totalSent = $reminderStats['sent'] + $overdueStats['sent'];
        $totalErrors = $reminderStats['errors'] + $overdueStats['errors'];

        $this->info("📧 Total emails envoyés: {$totalSent}");
        $this->info("   • Rappels préventifs: {$reminderStats['sent']}");
        $this->info("   • Notifications de retard: {$overdueStats['sent']}");

        if ($totalErrors > 0) {
            $this->error("❌ Total erreurs: {$totalErrors}");
        }

        if ($overdueStats['already_notified'] > 0) {
            $this->info("🛡️  Doublons évités: {$overdueStats['already_notified']}");
        }

        Log::info("SendReminderEmails: Résumé - {$totalSent} emails envoyés, {$totalErrors} erreurs");
    }
}
