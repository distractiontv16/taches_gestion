<?php

namespace App\Console\Commands;

use App\Mail\ReminderMail;
use App\Mail\TaskReminderMail;
use App\Models\Reminder;
use App\Models\Task;
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
    protected $description = 'Send emails for upcoming reminders and overdue tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info("Commande d'envoi d'emails lancée...");
            Log::info("Commande d'envoi d'emails lancée");
            
            $this->sendReminderEmails();
            $this->sendOverdueTaskReminders();
            
            $this->info("Commande terminée avec succès");
            Log::info("Commande terminée avec succès");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi des emails: ' . $e->getMessage());
            Log::error('Erreur lors de l\'envoi des emails: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return 1;
        }
    }
    
    private function sendReminderEmails()
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $oneHourAgo = Carbon::now()->subMinutes(30);
        
        $this->info("Recherche des rappels pour aujourd'hui: $today");
        
        // Get reminders for today that are within the past 30 minutes and haven't been emailed yet
        $reminders = Reminder::whereDate('date', $today)
            ->where('email_sent', false)
            ->get();
            
        $sent = 0;
        
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
                
                $this->info("Date/heure du rappel: " . $reminderDate->format('Y-m-d H:i:s'));
                
                // Check if the reminder time was within the last 30 minutes
                if ($reminderDate->isBetween($oneHourAgo, $now)) {
                    $this->info("Envoi d'un email pour le rappel: {$reminder->title}");
                    
                    try {
                        // Send the email
                        Mail::to($reminder->user->email)->send(new ReminderMail($reminder));
                        
                        // Mark as sent
                        $reminder->email_sent = true;
                        $reminder->save();
                        
                        $sent++;
                        
                        $this->info("Email envoyé pour: {$reminder->title} à {$reminder->user->email}");
                    } catch (\Exception $e) {
                        $this->error("Erreur lors de l'envoi de l'email pour {$reminder->title}: " . $e->getMessage());
                        Log::error("Erreur lors de l'envoi de l'email pour {$reminder->title}: " . $e->getMessage());
                    }
                } else {
                    $this->info("Rappel hors de la plage d'envoi: " . $reminderDate->format('Y-m-d H:i:s'));
                }
            } catch (\Exception $e) {
                $this->error("Erreur lors du traitement du rappel {$reminder->id}: " . $e->getMessage());
                Log::error("Erreur lors du traitement du rappel {$reminder->id}: " . $e->getMessage());
            }
        }
        
        $this->info("Envoi de $sent emails de rappel terminé");
    }
    
    private function sendOverdueTaskReminders()
    {
        $now = Carbon::now();
        $thirtyMinutesAgo = Carbon::now()->subMinutes(30);
        $thirtyFiveMinutesAgo = Carbon::now()->subMinutes(35);
        
        $this->info("Recherche des tâches en retard depuis 30 minutes");
        $this->info("Période de recherche: de " . $thirtyFiveMinutesAgo->format('Y-m-d H:i:s') . " à " . $thirtyMinutesAgo->format('Y-m-d H:i:s'));
        
        // Get tasks that were due 30 minutes ago and are not completed
        $overdueTasks = Task::where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->get();
            
        $sent = 0;
        
        foreach ($overdueTasks as $task) {
            try {
                // Convertir la date d'échéance en objet Carbon si ce n'est pas déjà fait
                $dueDate = $task->due_date instanceof Carbon ? $task->due_date : Carbon::parse($task->due_date);
                
                $this->info("Tâche: {$task->title} - Date d'échéance: " . $dueDate->format('Y-m-d H:i:s'));
                
                // Vérifier si la tâche était due entre 30 et 35 minutes dans le passé
                if ($dueDate->between($thirtyFiveMinutesAgo, $thirtyMinutesAgo)) {
                    $this->info("Tâche en retard détectée: {$task->title}");
                    
                    try {
                        // Send the email
                        Mail::to($task->user->email)->send(new TaskReminderMail($task));
                        
                        $sent++;
                        
                        $this->info("Email de rappel de tâche non validée envoyé pour: {$task->title} à {$task->user->email}");
                    } catch (\Exception $e) {
                        $this->error("Erreur lors de l'envoi de l'email pour la tâche {$task->title}: " . $e->getMessage());
                        Log::error("Erreur lors de l'envoi de l'email pour la tâche {$task->title}: " . $e->getMessage());
                    }
                } else {
                    $this->info("Tâche hors de la plage de rappel (pas encore 30 minutes de retard): {$task->title}");
                }
            } catch (\Exception $e) {
                $this->error("Erreur lors du traitement de la tâche {$task->id}: " . $e->getMessage());
                Log::error("Erreur lors du traitement de la tâche {$task->id}: " . $e->getMessage());
            }
        }
        
        $this->info("Envoi de $sent emails de rappel de tâches non validées terminé");
    }
}
