<?php

namespace App\Models;

use App\Models\Counselor;
use App\Models\UserComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselorLikeUserComment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'counselor_id', 'user_comment_id'];

    public function counselor(){
        return $this->belongsTo(Counselor::class);
    }

    public function userComment(){
        return $this->belongsTo(UserComment::class);
    }
}
