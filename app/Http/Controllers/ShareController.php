<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Share;

class ShareController extends Controller
{
     public function increaseShareCount(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                throw new \Exception('User not authenticated', 401);
            }

            $share = Share::updateOrCreate(
                ['user_id' => $user->id],
                ['count' => \DB::raw('count + 1')]
            );

            return response()->json(['message' => 'Share count increased successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
