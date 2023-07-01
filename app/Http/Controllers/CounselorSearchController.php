<?php

namespace App\Http\Controllers;

use App\Models\Counselor;
use Illuminate\Http\Request;

class CounselorSearchController extends Controller
{
    public function searchUsername(Request $request){
        $searchTerm = $request->search;

        if(!$searchTerm || $searchTerm == ""){
            $response = [
                'message' => 'Enter counselor username'
            ];

            return response()->json($response, 404);
        }


        $username = Counselor::where('username', 'LIKE', "%{$searchTerm}%")->get();

        $response = [
            'message' => $username
        ];

        return response()->json($response, 200);
    }

    public function searchField(Request $request){
        $searchTerm = $request->search;

        if(!$searchTerm || $searchTerm == ""){
            $response = [
                'message' => 'Enter counseling field'
            ];

            return response()->json($response, 404);
        }

        $field = Counselor::where('counseling_field', 'LIKE', "%{$searchTerm}%")->get();

        $response = [
            'message' => $field
        ];

        return response()->json($response, 200);
    }
}
