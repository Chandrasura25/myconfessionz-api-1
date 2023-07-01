<?php

namespace App\Models;

use App\Models\User;
use App\Models\CounselorReplyUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLikeCounselorReply extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'counselor_reply_user_id', 'user_comment_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function counselorReplyUser(){
        return $this->belongsTo(CounselorReplyUser::class);
    }
}
