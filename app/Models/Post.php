<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserComment;
use App\Models\UserLikePost;
use App\Models\CounselorComment;
use App\Models\CounselorLikePost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'post',
        'category',
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function userComments(){
        return $this->hasMany(UserComment::class);
    }

    public function counselorComments(){
        return $this->hasMany(CounselorComment::class);
    }

    public function userLikes(){
        return $this->hasMany(UserLikePost::class);
    }

    public function counselorLikes(){
        return $this->hasMany(CounselorLikePost::class);
    }

}
