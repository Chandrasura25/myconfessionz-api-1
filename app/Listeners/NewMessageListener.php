<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\Message;

class NewMessageListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */

    public function handle(MessageSent $event)
    {
        $message = new Message();
        $message->user_id = $event->message->sender_id;
        $message->counselor_id = $event->message->recipient_id;
        $message->content = $event->message->content;
        $message->save();

        // If the user is a counselor, create a new message as a reply from the counselor to the user
        if ($event->message->sender->role === 'counselor') {
            $reply = new Message();
            $reply->user_id = $event->message->recipient_id;
            $reply->counselor_id = $event->message->sender_id;
            $reply->content = $event->message->content;
            $reply->save();
        }
    }

}
