<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Session;
use Illuminate\Http\Request;

class CounsellorChatController extends Controller
{
public function getAllActiveUsers()
{
    // Check if the counselor is authenticated
    if (auth()->check()) {
        // Get the authenticated counselor
        $counselor = auth()->user();

        // Retrieve all active sessions for the counselor
        $activeSessions = Session::where('counselor_id', $counselor->id)
                                 ->where('status', true)
                                 ->with('user') 
                                 ->get();

        // Check if there are active sessions
        if ($activeSessions->isNotEmpty()) {
            // Extract users and include session ID
            $users = $activeSessions->map(function ($session) {
                return [
                    'session_id' => $session->id,
                    'user' => $session->user
                ];
            });

            // Extract unique users based on user ID
            $uniqueUsers = $users->unique('user.id')->values();

            return response()->json(['active_users' => $uniqueUsers], 200);
        } else {
            // No active sessions found
            return response()->json(['error' => 'No active sessions found'], 404);
        }
    } else {
        // Counselor is not authenticated
        return response()->json(['error' => 'Unauthenticated'], 401);
    }
}


  public function getConversations()
{
    $counselor = auth()->user();

    // Retrieve the conversations where the counselor is the sender or receiver and there is an approved session
    $conversations = Conversation::where(function ($query) use ($counselor) {
        $query->where('sender_id', $counselor->id)
              ->orWhere('receiver_id', $counselor->id);
    })
    ->whereHas('session', function ($query) {
        $query->where('status', true);
    })
    ->get();

    // Prepare an array to store simplified conversation data
    $simplifiedConversations = [];

    // Loop through conversations and extract sender and receiver details
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
    
    public function endSession(Request $request)
    {
        $counselorId = auth()->user()->id;
        $userId = $request->user_id;
        $sessionId = $request->session_id;

        $session = Session::where('id', $sessionId)
                          ->where('counselor_id', $counselorId)
                          ->where('user_id', $userId)
                          ->where('status', true) 
                          ->first();

        if ($session) {
            // End the session
            $session->status = false;
            $session->save();

            // Increment counselor's earnings by 3000
            $counselor = Counselor::find($counselorId);
            $counselor->earnings += 3000;
            $counselor->save();

            // Increment counseled clients count by one
            $counselor->counseled_clients += 1;
            $counselor->save();

            return response()->json(['message' => 'Session ended successfully'], 200);
        } else {
            return response()->json(['error' => 'Session not found or unauthorized'], 404);
        }
    }
    public function checkSession($userId)
    {
        if (auth()->check()) {
            $counselor = auth()->user();

            $activeSession = Session::where('counselor_id', $counselor->id)
                                    ->where('user_id', $userId)
                                    ->where('status', true)
                                    ->first();

            if ($activeSession) {
                return response()->json(['session' => $activeSession], 200);
            } else {
                return response()->json(['error' => 'No active session found with this user'], 404);
            }
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
}
