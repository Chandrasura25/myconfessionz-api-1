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

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $user;
    public $message;
    public $conversation;
    public $counselor;

    /**
     * Create a new event instance.
     *
     * @param  User  $user
     * @param  Message  $message
     * @param  Counselor $counselor
     * @param  Conversation  $conversation
     */
    public function __construct(User $user, Message $message, Conversation $conversation, Counselor $counselor)
    {
        $this->user = $user;
        $this->message = $message;
        $this->conversation = $conversation;
        $this->counselor = $counselor;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
   public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }
    public function broadcastAs()
    {
        return 'message.read';
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
