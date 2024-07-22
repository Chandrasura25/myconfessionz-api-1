<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Session;
use App\Models\Counselor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Firestore;

class CounsellorChatController extends Controller
{
    protected $firestore;

    public function __construct(Firestore $firestore)
    {
        $this->firestore = $firestore;
    }


    public function getAllActiveUsers()
    {   
        if (auth()->check()) {
            $counselor = auth()->user();
            $activeSessions = Session::where('counselor_id', $counselor->id)
                                     ->where('status', true)
                                     ->with('user')
                                     ->get();

            if ($activeSessions->isNotEmpty()) {
                $users = $activeSessions->map(function ($session) {
                    return [
                        'session_id' => $session->id,
                        'user' => $session->user
                    ];
                });

                $uniqueUsers = $users->unique('user.id')->values();

                return response()->json(['active_users' => $uniqueUsers], 200);
            } else {
                return response()->json(['error' => 'No active sessions found'], 404);
            }
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function getConversations()
    {
        $counselor = auth()->user();
        $conversations = Conversation::where(function ($query) use ($counselor) {
            $query->where('sender_id', $counselor->id)
                  ->orWhere('receiver_id', $counselor->id);
        })
        ->whereHas('session', function ($query) {
            $query->where('status', true);
        })
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
        $counselor = auth()->user();
        $firestoreMessages = $this->firestore->collection('chats')
            ->document($conversationId)
            ->collection('messages')
            ->orderBy('created_at')
            ->documents();

        $messages = [];
        foreach ($firestoreMessages as $message) {
            if ($message->exists()) {
                $messageData = $message->data();
                if ($messageData['sender_id'] == $counselor->id || $messageData['receiver_id'] == $counselor->id) {
                    $messages[] = [
                        'id' => $message->id(),
                        'sender_id' => $messageData['sender_id'],
                        'receiver_id' => $messageData['receiver_id'],
                        'sender_type' => $messageData['sender_type'],
                        'content' => $messageData['content'],
                        'created_at' => $messageData['created_at'],
                    ];
                }
            }
        }

        return response()->json([
            'messages' => $messages,
        ], 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'content' => 'required',
            'receiver_id' => 'required',
        ]);

        $counselor = auth()->user();
        $receiverId = $request->receiver_id;
        $content = $request->content;

        // Find or create the conversation
        $conversation = Conversation::firstOrCreate(
            [
                ['sender_id', '=', $counselor->id],
                ['receiver_id', '=', $receiverId]
            ],
            [
                ['sender_id', '=', $receiverId],
                ['receiver_id', '=', $counselor->id]
            ]
        );

        // Make sure the user exists
        $user = User::find($receiverId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Create a new message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $counselor->id,
            'receiver_id' => $receiverId,
            'sender_type' => 'counselor',
            'read' => false,
            'content' => $content,
            'type' => 'text',
        ]);

        // Save the message to Firestore
        $firebaseMessage = $this->firestore->collection('chats')
            ->document($conversation->id)
            ->collection('messages')
            ->add([
                'sender_id' => $counselor->id,
                'receiver_id' => $receiverId,
                'sender_type' => 'counselor',
                'content' => $content,
                'created_at' => now()->timestamp,
            ]);

        broadcast(new MessageSent($counselor, $message, $conversation, $user))->toOthers();

        return response()->json([
            'conversation' => $conversation,
            'message' => $message,
            'firebase_message' => $firebaseMessage->snapshot()->data(),
        ], 200);
    }

    public function markAsRead($messageId)
    {
        $counselor = auth()->user();
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json([
                'error' => 'Message not found',
            ], 404);
        }
        if ($message->receiver_id === $counselor->id) {
            $message->read = true;
            $message->save();

            // Update Firestore
            $this->firestore->collection('chats')
                ->document($message->conversation_id)
                ->collection('messages')
                ->document($message->id)
                ->update([
                    ['path' => 'read', 'value' => true]
                ]);

            $user = User::find($message->sender_id);
            $conversation = Conversation::find($message->conversation_id);
            broadcast(new MessageRead($user, $message, $conversation, $counselor))->toOthers();
            return response()->json([
                'message' => $message,
                'read' => 'Message marked as read',
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized action',
            ], 403);
        }
    }

    public function deleteMessage($messageId)
    {
        $counselor = auth()->user();
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json([
                'error' => 'Message not found',
            ], 404);
        }
        if ($message->sender_id === $counselor->id) {
            $conversationId = $message->conversation_id;
            $message->delete();

            // Delete from Firestore
            $this->firestore->collection('chats')
                ->document($conversationId)
                ->collection('messages')
                ->document($messageId)
                ->delete();

            // Update conversation's last_time_message
            $conversation = Conversation::find($message->conversation_id);
            if ($conversation) {
                $lastMessage = $conversation->messages()->latest()->first();
                if (!$lastMessage) {
                    $conversation->last_time_message = null;
                    $conversation->save();

                    // Update Firestore conversation last_time_message
                    $this->firestore->collection('chats')
                        ->document($conversationId)
                        ->update([
                            ['path' => 'last_time_message', 'value' => null]
                        ]);
                } else {
                    // Update Firestore conversation last_time_message
                    $this->firestore->collection('chats')
                        ->document($conversationId)
                        ->update([
                            ['path' => 'last_time_message', 'value' => $lastMessage->created_at->timestamp]
                        ]);
                }
            }
            return response()->json([
                'deleted' => 'Message deleted',
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized action',
            ], 403);
        }
    }

    public function getBalance()
    {
        $counselor = auth()->user();
        
        if (!$counselor) {
            return response()->json(['error' => 'Counselor not authenticated'], 401);
        }

        return response()->json(['balance' => $counselor->earnings], 200);
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
            $counselor = Counselor::find($counselorId);
            $counselor->earnings += 3000;
            $counselor->counseled_clients += 1;
            $counselor->save();

            foreach ($session->conversations as $conversation) {
                $conversation->messages()->delete();
                $conversation->delete();

                // Delete from Firestore
                $this->firestore->collection('chats')
                    ->document($conversation->id)
                    ->delete();
            }

            $session->delete();

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
