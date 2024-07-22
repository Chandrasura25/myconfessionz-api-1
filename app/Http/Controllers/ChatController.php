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
// use Kreait\Firebase\Firestore\FirestoreClient;
// use Kreait\Firebase\Contract\Firestore;

class ChatController extends Controller
{
    // protected $firestore;

    // public function __construct(FirestoreClient $firestore)
    // {
    //     $this->firestore = $firestore;
    // }

    public function getBalance()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        return response()->json(['balance' => $user->balance], 200);
    }

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
                        'session_id' => $session->id, // Assign session_id
                        'last_time_message' => now(),
                    ]);

                    // Save conversation to Firestore
                    $this->firestore->collection('conversations')
                        ->document($conversation->id)
                        ->set([
                            'sender_id' => $userId,
                            'receiver_id' => $counselorId,
                            'session_id' => $session->id,
                            'last_time_message' => now()->timestamp,
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
            'content' => 'required',
            'receiver_id' => 'required',
        ]);

        $user = Auth::user();
        $counselorId = $request->receiver_id;
        $content = $request->content;

        // Find or create the conversation
        $conversation = Conversation::firstOrCreate(
            [
                'sender_id' => $user->id,
                'receiver_id' => $counselorId
            ],
            [
                'sender_id' => $counselorId,
                'receiver_id' => $user->id
            ]
        );

        // Make sure the counselor exists
        $counselor = Counselor::find($counselorId);
        if (!$counselor) {
            return response()->json(['error' => 'Counselor not found'], 404);
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

        // Save the message to Firestore
        // $firestore = $this->firestore;
        // $documentRef = $firestore->collection('conversations')->document($conversation->id);
        // $messageRef = $documentRef->collection('messages')->add([
        //     'sender_id' => $user->id,
        //     'receiver_id' => $counselor->id,
        //     'sender_type' => 'user',
        //     'content' => $content,
        //     'created_at' => now()->timestamp,
        // ]);

        broadcast(new MessageSent($user, $message, $conversation, $counselor))->toOthers();

        return response()->json([
            'conversation' => $conversation,
            'message' => $message,
            // 'firebase_message' => $messageRef->snapshot()->data(),
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

        // Retrieve messages from Firestore
        $firestoreMessages = $this->firestore->collection('conversations')
            ->document($conversationId)
            ->collection('messages')
            ->orderBy('created_at')
            ->documents();

        $messages = [];
        foreach ($firestoreMessages as $message) {
            if ($message->exists()) {
                $messageData = $message->data();
                if ($messageData['sender_id'] == $user->id || $messageData['receiver_id'] == $user->id) {
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

            // Update Firestore
            $this->firestore->collection('conversations')
                ->document($message->conversation_id)
                ->collection('messages')
                ->document($message->id)
                ->update([
                    ['path' => 'read', 'value' => true]
                ]);

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
            $conversationId = $message->conversation_id;
            $message->delete();

            // Delete from Firestore
            $this->firestore->collection('conversations')
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
                    $this->firestore->collection('conversations')
                        ->document($conversationId)
                        ->update([
                            ['path' => 'last_time_message', 'value' => null]
                        ]);
                } else {
                    // Update Firestore conversation last_time_message
                    $this->firestore->collection('conversations')
                        ->document($conversationId)
                        ->update([
                            ['path' => 'last_time_message', 'value' => $lastMessage->created_at->timestamp]
                        ]);
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

            // Delete from Firestore
            $this->firestore->collection('conversations')
                ->document($conversationId)
                ->delete();

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
