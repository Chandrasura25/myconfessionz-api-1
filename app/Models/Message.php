<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'sender_type',
        'read',
        'content',
        'type',
    ];
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        if ($this->sender_type === 'user') {
            return $this->belongsTo(User::class, 'sender_id');
        } elseif ($this->sender_type === 'counselor') {
            return $this->belongsTo(Counselor::class, 'sender_id');
        }

        return null;
    }

    public function receiver()
    {
        if ($this->sender_type === 'user') {
            return $this->belongsTo(Counselor::class, 'receiver_id');
        } elseif ($this->sender_type === 'counselor') {
            return $this->belongsTo(User::class, 'receiver_id');
        }

        return null;
    }
}
