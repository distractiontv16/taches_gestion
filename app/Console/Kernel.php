<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Générer les tâches routinières tous les jours à 6h00
        $schedule->command('app:generate-routine-tasks')->dailyAt('06:00');

        // Envoyer les emails de rappel toutes les heures
        $schedule->command('app:send-reminder-emails')->hourly();

        // Nettoyer les rappels expirés toutes les heures
        $schedule->command('app:clean-expired-reminders')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 