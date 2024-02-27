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
        if ($user->balance < 5000) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        try {
            DB::beginTransaction();
            $user->balance -= 5000;
            $user->save();
            
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
