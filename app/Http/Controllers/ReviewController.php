<?php

namespace App\Http\Controllers;
use App\Models\Review;

use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function createReview(Request $request, $id){
        $request->validate([
            'review' => 'required',
        ]);

        $formFields = ([
            'user_id' => auth()->user()->id,
            'counselor_id' => $id,
            'review' => $request->review,
        ]);

        $review = Review::create($formFields);

        $response = [
            "message" => $review
        ];

        return response()->json($response, 201);
    }

    public function singleCounselorReviews($id){
        $review = Review::where('counselor_id', $id)->with('user')->get();

        $allReviews = $review->count();

        $response = [
            "review" => $review,
            "allReviews" => $allReviews
        ];

        return response()->json($response, 200);
    }

    public function deleteReview($id){
        $user = Review::where('id', $id)->first();
        if($user->user_id != auth()->id()){
            $response = [
                "message" => "Unauthorized action"
            ];
            return response()->json($response, 401);
        }
        Review::destroy($id);

        $response = [
            "message" => "Review deleted!"
        ];
        return response()->json($response, 200);
    }
}
