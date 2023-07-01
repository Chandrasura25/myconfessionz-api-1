<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'last_time_message',
    ];

    public function sender()
    {
        if ($this->senderIsUser()) {
            return $this->belongsTo(User::class, 'sender_id');
        } else {
            return $this->belongsTo(Counselor::class, 'sender_id');
        }
    }

    public function receiver()
    {
        if ($this->senderIsUser()) {
            return $this->belongsTo(Counselor::class, 'receiver_id');
        } else {
            return $this->belongsTo(User::class, 'receiver_id');
        }
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    private function senderIsUser()
    {
        $lastMessage = $this->messages()->latest()->first();

        if (!$lastMessage) {
            return false;
        }

        return $lastMessage->sender_type === 'user' || $this->sender_id === $lastMessage->sender_id;  
    }

    public function scopeBetweenUserAndCounselor($query, $userId, $counselorId)
    {
        return $query->where(function ($q) use ($userId, $counselorId) {
            $q->where('sender_id', $userId)->where('receiver_id', $counselorId);
        })->orWhere(function ($q) use ($userId, $counselorId) {
            $q->where('sender_id', $counselorId)->where('receiver_id', $userId);
        });
    }
}

