<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Counselor;
use App\Models\Conversation;


class SessionController extends Controller
{
    public function processSession(Request $request, $id)
    {
        $user = auth()->user();
        $counselorId = $id;
        
        // Check if there is an existing session with the same counselor and status is true
        $existingSession = Session::where('user_id', $user->id)
                                   ->where('counselor_id', $counselorId)
                                   ->where('status', true)
                                   ->exists();

        if ($existingSession) {
            return response()->json(['message' => 'Session with this counselor already exists and is active'], 200);
        }

        if ($user->balance < 5000) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        try {
            DB::beginTransaction();
            
            // Deduct money from the user's balance
            $user->balance -= 5000;
            $user->save();
            
            // Create a new session
            $session = new Session();
            $session->user_id = $user->id;
            $session->counselor_id = $counselorId;
            $session->amount = 5000;
            $session->status = true;
            $session->save();

            DB::commit();

            return response()->json(['message' => 'Session processed successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process session'], 500);
        }
    }
  
   public function getAllActiveSessions()
    {
        // Check if the user is authenticated
        if (auth()->check()) {
            // Get the authenticated user
            $user = auth()->user();

            // Retrieve all active sessions for the user
            $activeSessions = Session::where('user_id', $user->id)
                                     ->where('status', true)
                                     ->with('counselor') // Assuming there's a relationship defined in the Session model
                                     ->get();

            // If there are active sessions, return them
            if ($activeSessions->isNotEmpty()) {
                return response()->json(['active_sessions' => $activeSessions], 200);
            } else {
                // No active sessions found
                return response()->json(['error' => 'No active sessions found'], 404);
            }
        } else {
            // User is not authenticated
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
public function checkSession($counselor_id)
{
    if (auth()->check()) {
        $user = auth()->user();

        $activeSession = Session::where('user_id', $user->id)
                                ->where('counselor_id', $counselor_id)
                                ->where('status', true)
                                ->first();

        if ($activeSession) {
            return response()->json(['session' => $activeSession], 200);
        } else {
            return response()->json(['error' => 'No active session found with this counselor'], 404);
        }
    } else {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }
}


}
