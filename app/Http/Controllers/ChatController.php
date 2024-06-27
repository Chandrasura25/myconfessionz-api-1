<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Counselor;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Session;
class ChatController extends Controller
{
    public function initiateConversation(Request $request)
{
    $counselorId = $request->receiver_id;
    $userId = Auth::user()->id;

    // Check if a session exists with the specified counselor
    $session = Session::where('user_id', $userId)
        ->where('counselor_id', $counselorId)
        ->first();

    if ($session) {
        // Check if the session is approved (status is true)
        if ($session->status) {
            // Check if a conversation already exists between the user and counselor
            $conversation = Conversation::where('sender_id', $userId)
                ->where('receiver_id', $counselorId)
                ->first();
            $counselor = Counselor::find($counselorId);

            if (!$conversation) {
                // Create a new conversation
                $conversation = Conversation::create([
                    'sender_id' => $userId,
                    'receiver_id' => $counselorId,
                    'last_time_message' => now(),
                ]);

                return response()->json([
                    'conversation' => $conversation,
                    'counselor' => $counselor,
                ], 200);
            } else {
                // Conversation already exists, return the existing conversation
                return response()->json([
                    'conversation' => $conversation,
                    'counselor' => $counselor,
                ], 200);
            }
        } else {
            return response()->json(['error' => 'Session exists but not approved'], 400);
        }
    } else {
        return response()->json(['error' => 'Session not initiated or not approved'], 400);
    }
}
    public function sendMessage(Request $request)
    {
        $request->validate([
            "content" => 'required',
            "receiver_id" => 'required',
        ]);

        $user = Auth::user();
        $counselorId = $request->receiver_id;
        $content = $request->content;

        // Find the conversation between the user and counselor
        $conversation = Conversation::where(function ($query) use ($user, $counselorId) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $counselorId);
        })->orWhere(function ($query) use ($user, $counselorId) {
            $query->where('sender_id', $counselorId)
                ->where('receiver_id', $user->id);
        })->first();

        $counselor = Counselor::find($counselorId);
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
            'receiver_id' => $counselor->id,
            'sender_type' => 'user',
            'read' => false,
            'content' => $content,
            'type' => 'text',
        ]);

        // Fire the event for the new message sent
        broadcast(new MessageSent($user, $counselor, $message, $conversation))->toOthers();

        return response()->json([
            'conversation' => $conversation,
            "message" => $message,
        ], 200);
    }
    public function getUserConversations()
{
    $user = auth()->user();

    // Retrieve the conversations where the user is the sender or receiver and there is an approved session
    $conversations = Conversation::where(function ($query) use ($user) {
        $query->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
    })
    ->whereHas('session', function ($query) {
        $query->where('status', true);
    })
    ->get();

    // Prepare an array to store simplified conversation data
    $simplifiedConversations = [];

    // Loop through conversations and extract sender and receiver details
    foreach ($conversations as $conversation) {
        if ($conversation->sender_id === $user->id) {
            $sender = $conversation->senderUser;
            $receiver = $conversation->receiverCounselor;
        } else {
            $sender = $conversation->senderCounselor;
            $receiver = $conversation->receiverUser;
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

    public function markAsRead($messageId)
    {
        $user = Auth::user();

        // Find the message by ID
        $message = Message::find($messageId);

        // Make sure the message exists and belongs to the user
        if (!$message) {
            return response()->json([
                'error' => 'Message not found',
            ], 404);
        }
        if ($message->receiver_id === $user->id) {
            // Update the message as read
            $message->read = true;
            $message->save();
            $conversation = Conversation::find($message->conversation_id);
            $counselor = Counselor::find($conversation->receiver_id);
            broadcast(new MessageRead($user, $message, $conversation, $counselor))->toOthers();
            return response()->json([
                'message' => $message,
                'read' => 'Message marked as read',
            ], 200);
        }
    }
    public function deleteMessage($messageId)
    {
        $user = Auth::user();

        // Find the message by ID
        $message = Message::find($messageId);

        // Make sure the message exists and belongs to the user
        if (!$message) {
            return response()->json([
                'error' => 'Message not found',
            ], 404);
        }
        if ($message->sender_id === $user->id) {
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
    public function deleteConversation($conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            throw ValidationException::withMessages(['conversation_id' => 'Conversation not found']);
        }
        if ($conversation->sender_id !== $user->id && $conversation->receiver_id !== $user->id) {
            throw ValidationException::withMessages(['conversation_id' => 'You are not authorized to delete this conversation']);
        }
        DB::beginTransaction();

        try {
            $conversation->messages()->delete();
            $conversation->delete();
            DB::commit();

            return response()->json([
                'message' => 'Conversation deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while deleting the conversation',
            ], 500);
        }
    }
}
