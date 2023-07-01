<?php

namespace App\Listeners;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Events\MessageSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MessageSentListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        // Retrieve the data from the event
        $user = $event->user;
        $message = $event->message;
        $conversation = $event->conversation;
        $sender = $event->sender;
        $receiver = $event->receiver;

        // Broadcast the event to the channel
        $channelName = 'chat.' . $sender->id . '.' . $receiver->id;
        $data = [
            'user' => $user->toArray(),
            'message' => $message->toArray(),
            'conversation' => $conversation->toArray(),
            'sender' => $sender->toArray(),
            'receiver' => $receiver->toArray(),
        ];

        try {
            // Trigger the event using Pusher
            Broadcast::channel($channelName)->whisper('client-event', [ 
                'event' => 'message-sent',
                'data' => $data,
            ]);

            // Log successful event broadcasting
            Log::info('MessageSent event broadcasted successfully.'); 
        } catch (\Exception $e) {
            // Log any errors that occur during event broadcasting
            Log::error('Failed to broadcast MessageSent event: ' . $e->getMessage());
        }
    }
}
