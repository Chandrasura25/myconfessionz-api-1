<?php

namespace App\Models;

use App\Models\Post;
use App\Models\Counselor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselorLikePost extends Model
{
    use HasFactory;

    protected $fillable = ['counselor_id', 'post_id'];

    public function counselor(){
        return $this->belongsTo(Counselor::class);
    }

    public function post(){
        return $this->belongsTo(Post::class);
    }
}
