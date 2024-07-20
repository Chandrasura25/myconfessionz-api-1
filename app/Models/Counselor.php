<?php

namespace App\Models;

use App\Models\Counselorcomment;
use App\Models\CounselorLikePost;
use App\Models\CounselorReplyUser;
use App\Models\CounselorLikeUserReply;
use App\Models\CounselorLikeUserComment;
use App\Models\CounselorLikeCounselorReply;
use App\Models\CounselorLikeCounselorComment;
use App\Models\Review;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Message;
use App\Models\ShareAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Counselor extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'counselors';
    protected $fillable = [
        "username",
        "first_name",
        "last_name",
        "image",
        "counseled_clients",
        "counseling_field",
        "earnings",
        "satisfied_clients",
        'dob',
        'gender',
        'password',
        'country',
        'state',
        'recovery_question1',
        'answer1',
        'recovery_question2',
        'answer2',
        'recovery_question3',
        'answer3',
        'verified',
        'bio'
    ];

    public function counselorComments(){
        return $this->hasMany(Counselorcomment::class);
    }

    public function counselorRepyUsers(){
        return $this->hasMany(CounselorReplyUser::class);
    }

    public function counselorLikePosts(){
        return $this->hasMany(CounselorLikePost::class);
    }

    public function counselorLikeUserComments(){
        return $this->hasMany(CounselorLikeUserComment::class);
    }

    public function counselorLikeCounselorComments(){
        return $this->hasMany(CounselorLikeCounselorComment::class);
    }

    public function counselorLikeUserReplies(){
        return $this->hasMany(CounselorLikeUserReply::class);
    }

    public function counselorLikeCounselorReplies(){
        return $this->hasMany(CounselorLikeCounselorReply::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
   public function shareActions()
    {
        return $this->morphMany(ShareAction::class, 'shareable');
    }
}
