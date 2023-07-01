<?php

namespace App\Models;

use App\Models\UserPost;
use App\Models\User;
use App\Models\UserReply;
use App\Models\UserLikeUserComment;
use App\Models\CounselorReply;
use App\Models\CounselorLikeUserComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'user_comment'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function post(){
        return $this->belongsTo(Post::class);
    }

    public function userLikes(){
        return $this->hasMany(UserLikeUserComment::class);
    }

    public function counselorLikes(){
        return $this->hasMany(CounselorLikeUserComment::class);
    }

    public function userReplies(){
        return $this->hasMany(UserReplyUser::class);
    }

    public function counselorReplies(){
        return $this->hasMany(CounselorReplyUser::class);
    }
}
