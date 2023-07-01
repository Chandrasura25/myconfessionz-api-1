<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserReplyUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLikeUserReply extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'user_reply_user_id', 'user_comment_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function userReplyUser(){
        return $this->belongsTo(UserReplyUser::class);
    }
}
