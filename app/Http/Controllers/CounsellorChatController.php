<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class CounsellorChatController extends Controller
{
    public function getConversations()
    {
        $counselor = auth()->user();

        $conversations = $counselor->conversations;

        return response()->json([
            'conversations' => $conversations,
        ], 200);
    }
    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Check if the authenticated counselor is a participant in the conversation
        if ($conversation->counselor_id !== auth()->user()->id) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        $messages = $conversation->messages;

        return response()->json([
            'messages' => $messages,
        ], 200);
    }

    public function sendMessage(Request $request)
    {
        $counselor = auth()->user();
        $message = new Message();
        $conversation = new Conversation();

        // Determine the sender and receiver based on the sender_type
        if ($request->input('sender_type') === 'counselor') {
            $sender = $counselor;
            $receiver = $conversation->user;
        } else {
            $sender = $conversation->user;
            $receiver = $counselor;
        }

        // Create a new message
        $newMessage = $message->create([
            'conversation_id' => $request->input('conversation_id'),
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'sender_type' => $request->input('sender_type'),
            'read' => false,
            'content' => $request->input('content'),
            'type' => 'text',
        ]);

        // Broadcast the message to the other participant(s)
        event(new MessageSent($sender, $newMessage, $conversation));

        return response()->json([
            'message' => $newMessage,
        ], 200);
    }
    public function markAsRead($messageId)
    {
        $counselor = auth()->user();

        // Find the message by ID
        $message = Message::find($messageId);

        // Make sure the message exists and belongs to the counselor
        if (!$message || $message->receiver_id !== $counselor->id) {
            return response()->json([
                'error' => 'Message not found',
            ], 404);
        }

        // Update the message as read
        $message->read = true;
        $message->save();

        return response()->json([
            'message' => 'Message marked as read',
        ], 200);
    }

}
