<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function initiateConversation(Request $request)
    {
        $counselorId = $request->input('counselor_id');
        $userId = Auth::id();

        // Check if a conversation already exists between the user and counselor
        $conversation = Conversation::where('sender_id', $userId)
            ->where('receiver_id', $counselorId)
            ->first();

        if (!$conversation) {
            // Create a new conversation
            $conversation = Conversation::create([
                'sender_id' => $userId,
                'receiver_id' => $counselorId,
                'last_time_message' => now(),
            ]);

            return response()->json([
                'conversation' => $conversation,
            ], 200);
        } else {
            // Conversation already exists, return the existing conversation
            return response()->json([
                'conversation' => $conversation,
            ], 200);
        }
    }
    public function getUserConversations()
    {
        $user = auth()->user();

        // Retrieve the conversations where the user is the sender or receiver
        $conversations = Conversation::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })->get();

        return response()->json([
            'conversations' => $conversations,
        ], 200);
    }
    public function getMessages($conversationId)
    {
        $user = auth()->user();

        // Retrieve the messages within the conversation for the authenticated user
        $messages = Message::where('conversation_id', $conversationId)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->get();

        return response()->json([
            'messages' => $messages,
        ], 200);
    }

    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        $counselorId = $request->input('counselor_id');
        $content = $request->input('content');

        // Find the conversation between the user and counselor
        $conversation = Conversation::where('sender_id', $user->id)
            ->where('receiver_id', $counselorId)
            ->first();

        // Make sure the conversation exists
        if (!$conversation) {
            return response()->json([
                'error' => 'Conversation not found',
            ], 404);
        }

        // Create a new message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'receiver_id' => $counselorId,
            'sender_type' => 'user',
            'read' => false,
            'content' => $content,
            'type' => 'text',
        ]);

        // Fire the event for the new message sent
        event(new MessageSent($user, $message, $conversation));

        return response()->json([
            'message' => 'Message sent successfully',
        ], 200);
    }
    public function markAsRead($messageId)
    {
        $user = Auth::user();
    
        // Find the message by ID
        $message = Message::find($messageId);
    
        // Make sure the message exists and belongs to the user
        if (!$message || $message->receiver_id !== $user->id) {
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
