<?php

namespace App\Models;

use App\Models\Counselor;
use App\Models\CounselorReplyUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselorLikeCounselorReply extends Model
{
    use HasFactory;


    protected $fillable = ['post_id', 'counselor_id', 'counselor_reply_user_id', 'user_comment_id'];

    public function counselor(){
        return $this->belongsTo(Counselor::class);
    }

    public function counselorReplyUser(){
        return $this->belongsTo(CounselorReplyUser::class);
    }
}
