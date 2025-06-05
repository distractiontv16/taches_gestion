<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_to',
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }



    // Add relationship to reminders
    public function reminders()
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'to_do':
                return 'primary';
            case 'in_progress':
                return 'warning';
            case 'completed':
                return 'success';
            default:
                return 'secondary';
        }
    }

    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class);
    }

    // Accesseur pour convertir la date d'échéance en objet Carbon
    public function getDueDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    // Vérifie si la tâche est en retard
    public function getIsOverdueAttribute()
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }
        
        return $this->due_date->isPast();
    }
    
    // Vérifie si la tâche est imminente (prévue dans les 24 heures)
    public function getIsUpcomingAttribute()
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }
        
        return $this->due_date->isFuture() && $this->due_date->diffInHours(now()) <= 24;
    }
}
