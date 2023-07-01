<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLikeUserComment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'user_comment_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function userComment(){
        return $this->belongsTo(UserComment::class);
    }
}
