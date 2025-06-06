<?php
namespace App\Models;

use App\Notifications\ResetPassword;
use App\Traits\EncryptableFields;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, EncryptableFields;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }



    /**
     * Get the tasks for the user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the routines for the user.
     */
    public function routines()
    {
        return $this->hasMany(Routine::class);
    }

    /**
     * Get the notes for the user.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get the calendar events for the user.
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }


}
