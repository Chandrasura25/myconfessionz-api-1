<?php

namespace App\Http\Controllers;

use App\Models\CounselorReplyUser;
use App\Models\UserReplyUser;
use Illuminate\Http\Request;

class UserReplyController extends Controller
{
    public function userReplyUser(Request $request, $id){
        $request->validate([
            'reply' => "required"
        ]);

        $formFields = ([
            "user_id" => auth()->user()->id,
            "user_comment_id" => $id,
            "user_reply" => $request->reply
        ]);

        UserReplyUser::create($formFields);

        $response = [
            "message" => "Comment replied!"
        ];

        return response()->json([$response], 201);
    }

    // public function allCommentReplies($id){
    //     $commentReplies = UserReplyUser::where("user_comment_id", $id)->get();
    //     $commentRepliesCount = UserReplyUser::where("user_comment_id", $id)->get()->count();

    //     $response = [
    //         "commentReplies" => $commentReplies,
    //         "commentRepliesCount" => $commentRepliesCount,
    //     ];

    //     return response()->json($response, 200);
    // }


    public function userReplyUserComment($id)
    {
        $userReply = UserReplyUser::with([
            'user',
            'userComment' => function ($query) {
                $query->orderBy('created_at', 'desc'); // Order user comments by created_at in descending order
            },
            'userLikes',
            'counselorLikes'
        ])
        ->withCount('userLikes', 'counselorLikes')
        ->where('id', $id) // Find the reply by ID
        ->orderBy('created_at', 'desc') // Order the counselor reply by created_at in descending order
        ->firstOrFail(); // Find the reply by ID or throw a 404 error
    
        $response = [
            'userReply' => $userReply
        ];
    
        return response()->json($response, 200); // Return the response as JSON
    }
    
    public function counselorReplyUserComment($id)
    {
        $counselorReply = CounselorReplyUser::with([
            'counselor',
            'userComment' => function ($query) {
                $query->orderBy('created_at', 'desc'); // Order user comments by created_at in descending order
            },
            'userLikes',
            'counselorLikes'
        ])
        ->withCount('userLikes', 'counselorLikes')
        ->where('id', $id) // Find the reply by ID
        ->orderBy('created_at', 'desc') // Order the counselor reply by created_at in descending order
        ->firstOrFail(); // Get the first matching reply or throw a 404 error if not found
    
        $response = [
            'counselorReply' => $counselorReply,
        ];
    
        return response()->json($response, 200); // Return the response as JSON
    }
    
    

    public function userDeleteReply($id){
        $user = UserReplyUser::where('id', $id)->first();

        if($user->user_id != auth()->user()->id){
            $response = [
                "message" => "Unauthorized action"
            ];
            return response()->json($response, 401);
        }
        UserReplyUser::destroy($id);

        $response = [
            "message" => "deleted!"
        ];
        return response()->json($response, 200);
    }
}
