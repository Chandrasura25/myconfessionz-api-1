<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class CounsellorChatController extends Controller
{
    public function getConversations()
{
    $counselor = auth()->user();

    $conversations = Conversation::where('sender_id', $counselor->id)
        ->orWhere('receiver_id', $counselor->id)
        ->get();

    $simplifiedConversations = [];

    foreach ($conversations as $conversation) {
        if ($conversation->sender_id === $counselor->id) {
            $sender = $conversation->senderCounselor;
            $receiver = $conversation->receiverUser;
        } else {
            $sender = $conversation->senderUser;
            $receiver = $conversation->receiverCounselor;
        }

        $simplifiedConversations[] = [
            'id' => $conversation->id,
            'sender_id' => $conversation->sender_id,
            'receiver_id' => $conversation->receiver_id,
            'last_time_message' => $conversation->last_time_message,
            'created_at' => $conversation->created_at,
            'updated_at' => $conversation->updated_at,
            'sender' => $sender,
            'receiver' => $receiver,
        ];
    }

    return response()->json([
        'conversations' => $simplifiedConversations,
    ], 200);
}


    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        $messages = $conversation->messages;

        return response()->json([
            'messages' => $messages,
        ], 200);

    }
    public function markAsRead($messageId)
    {
        $counselor = auth()->user();

        // Find the message by ID
        $message = Message::find($messageId);

        // Make sure the message exists and belongs to the counselor
        if (!$message) {
            return response()->json([
                'error' => 'Message not found',
            ], 404);
        }
        if ($message->receiver_id === $counselor->id) {
            // Update the message as read
            $message->read = true;
            $message->save();
            $user = User::find($message->sender_id);
            $conversation = Conversation::find($message->conversation_id);
            broadcast(new MessageRead($user, $message, $conversation, $counselor))->toOthers();
            return response()->json([
                'message' => $message,
                'read' => 'Message marked as read',
            ], 200);
        }
    }
   
    public function sendMessage(Request $request)
    {
        $request->validate([
            "content" => 'required',
            "receiver_id" => 'required',
        ]);
    
        $counselor = auth()->user();
        $userId = $request->receiver_id;
    
        // Find or create the conversation between the user and counselor
        $conversation = Conversation::where(function ($query) use ($userId, $counselor) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', $counselor->id);
        })->orWhere(function ($query) use ($userId, $counselor) {
            $query->where('sender_id', $counselor->id)
                ->where('receiver_id', $userId);
        })->orderBy('created_at', 'desc')->first();
    
        if (!$conversation) {
            return response()->json([
                'error' => 'Conversation not found',
            ], 404);
        }
    
        // Create a new message
        $message = new Message([
            'conversation_id' => $conversation->id,
            'sender_id' => $counselor->id,
            'receiver_id' => $userId,
            'sender_type' => 'counselor',
            'read' => false,
            'content' => $request->content,
            'type' => 'text',
        ]);
        $message->save();
    
        $user = User::find($userId);
        
        // Broadcast the message to the other participant(s)
        broadcast(new MessageSent($user, $counselor, $message, $conversation))->toOthers();
    
        return response()->json([
            'message' => $message,
            'conversation' => $conversation,
        ], 200);
    }
    public function deleteMessage($messageId)
    {
        $counselor = auth()->user();

        // Find the message by ID
        $message = Message::find($messageId);

        // Make sure the message exists and belongs to the user
        if (!$message) {
            return response()->json([
                'error' => 'Message not found',
            ], 404);
        }
        if ($message->sender_id === $counselor->id) {
            $message->delete();
            // Update conversation's last_time_message
            $conversation = Conversation::find($message->conversation_id);
            if ($conversation) {
                $lastMessage = $conversation->messages()->latest()->first();
                if (!$lastMessage) {
                    $conversation->last_time_message = null;
                    $conversation->save();
                }
            }
            return response()->json([
                'deleted' => 'Message deleted',
            ], 200);
        }
    }
}
