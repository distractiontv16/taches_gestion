<?php
namespace App\Models;

use App\Traits\EncryptableFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory, EncryptableFields;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'date',
        'time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
