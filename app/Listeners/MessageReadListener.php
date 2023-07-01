<?php

namespace App\Listeners;

use App\Events\MessageRead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class MessageReadListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  MessageRead  $event
     * @return void
     */
    public function handle(MessageRead $event)
    {
        // Retrieve the necessary data from the event
        $user = $event->user;
        $message = $event->message;

        // Perform the necessary actions when a message is read
        // For example, you can update the 'read' status of the message
        $message->read = true;
        $message->save();

        // Trigger the message-read event using Pusher
        $data = [
            'event' => 'message-read',
            'data' => [
                'user' => $user->toArray(),
                'message' => $message->toArray(),
            ],
        ];

        try {
            // Trigger the event using Pusher
            \Illuminate\Support\Facades\Broadcast::channel('chat.' . $user->id . '.' . $message->sender_id)->whisper('client-event', $data);

            // Log successful event broadcasting
            Log::info('MessageRead event broadcasted successfully.');
        } catch (\Exception $e) {
            // Log any errors that occur during event broadcasting
            Log::error('Failed to broadcast MessageRead event: ' . $e->getMessage());
        }
    }
}
