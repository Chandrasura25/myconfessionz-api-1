<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Post;
use App\Models\UserComment;
use App\Models\UserLikePost;
use App\Models\UserReplyUser;
use App\Models\UserLikeUserReply;
use App\Models\UserLikeUserComment;
use App\Models\UserLikeCounselorReply;
use App\Models\UserLikeCounselorComment;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'usercode',
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
        'balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function userComments(){
        return $this->hasMany(UserComment::class);
    }

    public function userReplyusers(){
        return $this->hasMany(UserReplyUser::class);
    }

    public function userLikePosts(){
        return $this->hasMany(UserLikePost::class);
    }

    public function UserLikeUserComments(){
        return $this->hasMany(UserLikeUserComment::class);
    }

    public function UserLikeCounselorComments(){
        return $this->hasMany(UserLikeCounselorComment::class);
    }

    public function userLikeUserReplies(){
        return $this->hasMany(UserLikeUserReply::class);
    }

    public function userLikeCounselorReplies(){
        return $this->hasMany(UserLikeCounselorReply::class);
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
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'sender_id')->orWhere('receiver_id', $this->id);
    }
    // public function sentConversations()
    // {
    //     return $this->hasMany(Conversation::class, 'sender_id');
    // }

    // public function receivedConversations()
    // {
    //     return $this->hasMany(Conversation::class, 'receiver_id');
    // }
}
