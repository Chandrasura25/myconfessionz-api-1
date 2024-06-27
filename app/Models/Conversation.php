<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Conversation extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'last_time_message',
        'session_id'
    ];
     public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function senderCounselor(): BelongsTo
    {
        return $this->belongsTo(Counselor::class, 'sender_id');
    }

    public function receiverCounselor(): BelongsTo
    {
        return $this->belongsTo(Counselor::class, 'receiver_id');
    }

   public function session()
    {
        return $this->belongsTo(Session::class);
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

