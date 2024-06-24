<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\Counselor;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $user;
    public $message;
    public $conversation;
    public $sender;
    public $receiver;
    public $counselor;

    /**
     * Create a new event instance.
     *
     * @param  User  $user
     * @param  Message  $message
     * @param  Conversation  $conversation
     * @param  Counselor  $counselor
     */
    public function __construct(User $user, Counselor $counselor, Message $message, Conversation $conversation)
    {
        $this->user = $user;
        $this->message = $message;
        $this->conversation = $conversation;
        $this->counselor = $counselor;

        // Determine the sender and receiver based on the sender_type
        if ($message->sender_type === 'user') {
            $this->sender = $user;
            $this->receiver = $conversation->counselor;
        } else {
            $this->sender = $conversation->counselor;
            $this->receiver = $user;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
     public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->conversation->id);
    }
    public function broadcastAs()
    {  
        return 'message.sent';
     }
    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'user' => $this->user->toArray(),
            'message' => $this->message->toArray(),
            'conversation' => $this->conversation->toArray(),
            'counselor' => $this->counselor->toArray(),
        ];
    }
}
