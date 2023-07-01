<?php

namespace App\Models;

use App\Models\Counselor;
use App\Models\UserReplyUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselorLikeUserReply extends Model
{
    use HasFactory;


    protected $fillable = ['post_id', 'counselor_id', 'user_reply_user_id', 'user_comment_id'];

    public function counselor(){
        return $this->belongsTo(Counselor::class);
    }

    public function userReplyUser(){
        return $this->belongsTo(UserReplyUser::class);
    }
}
