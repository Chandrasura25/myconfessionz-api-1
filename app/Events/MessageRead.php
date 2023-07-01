<?php

namespace App\Events;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
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
    public $sender;
    public $receiver;
    public $counselor;

    /**
     * Create a new event instance.
     *
     * @param  User  $user
     * @param  Message  $message
     * @param  Conversation  $conversation
     */
    public function __construct(User $user, Message $message, Conversation $conversation)
    {
        $this->user = $user;
        $this->message = $message;
        $this->conversation = $conversation;

        // Determine the sender and receiver based on the sender_type
        if ($message->sender_type === 'user') {
            $this->sender = $user;
            $this->receiver = $conversation->counselor;
        } else {
            $this->sender = $conversation->counselor;
            $this->receiver = $user;
        }

        $this->counselor = $conversation->counselor;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channelName = 'chat.' . $this->sender->id . '.' . $this->receiver->id;
        return new PrivateChannel($channelName);
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
            'sender' => $this->sender->toArray(),
            'receiver' => $this->receiver->toArray(),
            'counselor' => $this->counselor->toArray(),
        ];
    }
}
