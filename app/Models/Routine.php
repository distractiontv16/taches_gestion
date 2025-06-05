<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Routine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'frequency',
        'days',
        'weeks',
        'months',
        'start_time',
        'end_time',
        'due_time',
        'workdays_only',
        'is_active',
        'priority',
        'last_generated_date',
        'total_tasks_generated',
    ];

    protected $casts = [
        'workdays_only' => 'boolean',
        'is_active' => 'boolean',
        'last_generated_date' => 'date',
        'total_tasks_generated' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generatedTasks()
    {
        return $this->hasMany(Task::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Vérifie si la routine doit générer une tâche pour une date donnée
     */
    public function shouldGenerateForDate(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Vérifier si déjà généré pour cette date
        if ($this->last_generated_date && $this->last_generated_date->isSameDay($date)) {
            return false;
        }

        // Vérifier les jours ouvrables
        if ($this->workdays_only && $date->isWeekend()) {
            return false;
        }

        return $this->matchesFrequencyForDate($date);
    }

    /**
     * Vérifie si la date correspond à la fréquence de la routine
     */
    private function matchesFrequencyForDate(Carbon $date): bool
    {
        switch ($this->frequency) {
            case 'daily':
                return $this->matchesDailyFrequency($date);
            case 'weekly':
                return $this->matchesWeeklyFrequency($date);
            case 'monthly':
                return $this->matchesMonthlyFrequency($date);
            default:
                return false;
        }
    }

    /**
     * Vérifie si la date correspond à la fréquence quotidienne
     */
    private function matchesDailyFrequency(Carbon $date): bool
    {
        if (!$this->days) {
            return true; // Tous les jours
        }

        $dayName = strtolower($date->format('l'));
        $selectedDays = json_decode($this->days, true) ?? [];

        return in_array($dayName, $selectedDays);
    }

    /**
     * Vérifie si la date correspond à la fréquence hebdomadaire
     */
    private function matchesWeeklyFrequency(Carbon $date): bool
    {
        if (!$this->weeks) {
            return false;
        }

        $weekOfYear = $date->weekOfYear;
        $selectedWeeks = json_decode($this->weeks, true) ?? [];

        return in_array($weekOfYear, $selectedWeeks);
    }

    /**
     * Vérifie si la date correspond à la fréquence mensuelle
     */
    private function matchesMonthlyFrequency(Carbon $date): bool
    {
        if (!$this->months) {
            return false;
        }

        $month = $date->month;
        $selectedMonths = json_decode($this->months, true) ?? [];

        return in_array($month, $selectedMonths);
    }

    /**
     * Calcule la date/heure d'échéance pour une tâche générée
     */
    public function calculateDueDateTime(Carbon $targetDate): Carbon
    {
        $dueTime = $this->due_time ?? $this->end_time ?? '23:59:00';

        return $targetDate->copy()->setTimeFromTimeString($dueTime);
    }

    /**
     * Marque la routine comme ayant généré des tâches pour une date
     */
    public function markAsGenerated(Carbon $date): void
    {
        $this->update([
            'last_generated_date' => $date,
            'total_tasks_generated' => $this->total_tasks_generated + 1
        ]);
    }
}
