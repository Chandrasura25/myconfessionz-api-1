<?php

namespace App\Models;

use App\Models\Counselor;
use App\Models\CounselorComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselorLikeCounselorComment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'counselor_id', 'counselor_comment_id'];

    public function counselor(){
        return $this->belongsTo(Counselor::class);
    }

    public function counselorComment(){
        return $this->belongsTo(CounselorComment::class);
    }
}
