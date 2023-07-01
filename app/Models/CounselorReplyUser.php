<?php

namespace App\Models;

use App\Models\Counselor;
use App\Models\UserComment;
use App\Models\UserLikeCounselorReply;
use App\Models\CounselorLikeCounselorReply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselorReplyUser extends Model
{
    use HasFactory;
    protected $fillable = ['counselor_reply', 'user_comment_id', 'counselor_id'];

    public function counselor(){
        return $this->belongsTo(Counselor::class);
    }

    public function userComment(){
        return $this->belongsTo(UserComment::class);
    }

    public function userLikes(){
        return $this->hasMany(UserLikeCounselorReply::class);
    }

    public function counselorLikes(){
        return $this->hasMany(CounselorLikeCounselorReply::class);
    }
}
