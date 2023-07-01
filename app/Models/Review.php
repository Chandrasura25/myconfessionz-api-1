<?php

namespace App\Models;

use App\Models\Counselor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['review', 'user_id', 'counselor_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function counselor(){
        return $this->belongsTo(Counselor::class);
    }
}
