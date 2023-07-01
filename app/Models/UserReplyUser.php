<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserComment;
use App\Models\UserLikeUserReply;
use App\Models\CounselorLikeUserReply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserReplyUser extends Model
{
    use HasFactory;

    protected $fillable = ['user_reply', 'user_comment_id', 'user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function userComment(){
        return $this->belongsTo(UserComment::class);
    }

    public function userLikes(){
        return $this->hasMany(UserLikeUserReply::class);
    }

    public function counselorLikes(){
        return $this->hasMany(CounselorLikeUserReply::class);
    }
}
