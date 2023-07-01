<?php

namespace App\Models;

use App\Models\User;
use App\Models\CounselorComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLikeCounselorComment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'counselor_comment_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function counselorComment(){
        return $this->belongsTo(CounselorComment::class);
    }
}
