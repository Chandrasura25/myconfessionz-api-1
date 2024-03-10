<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function processSession(Request $request)
    {
        $user = Auth::user();
        $counselorId = $request->counselor_id;
        
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
}
