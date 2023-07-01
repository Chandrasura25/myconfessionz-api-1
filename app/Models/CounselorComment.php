<?php

namespace App\Models;

use App\Models\Post;
use App\Models\Counselor;
use App\Models\UserLikeCounselorComment;
use App\Models\CounselorLikeCounselorComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselorComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'counselor_id',
        'post_id',
        'counselor_comment'
    ];

    public function counselor(){
        return $this->belongsTo(Counselor::class);
    }

    public function post(){
        return $this->belongsTo(Post::class);
    }

    public function userLikes(){
        return $this->hasMany(UserLikeCounselorComment::class);
    }

    public function counselorLikes(){
        return $this->hasMany(CounselorLikeCounselorComment::class);
    }
}
