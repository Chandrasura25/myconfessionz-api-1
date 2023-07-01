<?php

namespace App\Http\Controllers;

use App\Models\Counselor;
use Illuminate\Http\Request;

class CounselorManagementController extends Controller
{
    public function allCounselors(){
        $counselors = Counselor::inRandomOrder()->get();

        $response = [
            "counselors" => $counselors,
        ];

        return response()->json($response, 200);
    }

    public function singleCounselor($id){
        $counselor = Counselor::find($id);

        $response = [
            "counselor" => $counselor,
        ];

        return response()->json($response, 200);
    }

    public function counselorsByField($field){
        $counselors = Counselor::where('counseling_field', $field)->inRandomOrder()->get();

        $response = [
            "counselor" => $counselors,
        ];

        return response()->json($response, 200);
    }

    public function deleteAccount($id){
        // Find the user by ID
        $counselor = Counselor::find($id);

        // Check if the counselor exists
        if (!$counselor) {
            return response()->json(['message' => 'Counselor not found'], 404);
        }

        if($counselor->username != auth()->user()->username){
            $response = [
                'message' => "Unauthorized action!"
            ];

            return response()->json($response, 200);
        }

        // Delete the counselor
        $counselor->delete();

        return response()->json(['message' => 'Counselor account deleted'], 200);
    }
}
