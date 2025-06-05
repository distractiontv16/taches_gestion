<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanExpiredReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up reminders that have already passed their due date and time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i');
        
        // Trouver les rappels passés (soit la date est passée, soit la date est aujourd'hui mais l'heure est passée)
        $expiredReminders = Reminder::where(function ($query) use ($today, $currentTime) {
            $query->where('date', '<', $today)
                ->orWhere(function ($q) use ($today, $currentTime) {
                    $q->where('date', $today)
                      ->where('time', '<', $currentTime);
                });
        })->get();
        
        $count = $expiredReminders->count();
        
        // Option 1: Supprimer complètement les rappels expirés
        // Reminder::whereIn('id', $expiredReminders->pluck('id'))->delete();
        
        // Option 2: Marquer les rappels comme envoyés sans les supprimer
        Reminder::whereIn('id', $expiredReminders->pluck('id'))->update(['email_sent' => true]);
        
        $this->info("$count rappels expirés ont été traités.");
        
        return 0;
    }
} 