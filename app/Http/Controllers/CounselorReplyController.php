<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CounselorReplyUser;

class CounselorReplyController extends Controller
{
    public function counselorReplyUser(Request $request, $id){
        $request->validate([
            'reply' => "required"
        ]);

        $formFields = ([
            "counselor_id" => auth()->user()->id,
            "user_comment_id" => $id,
            "counselor_reply" => $request->reply
        ]);

        // dd($formFields);

        CounselorReplyUser::create($formFields);

        $response = [
            "message" => "replied!"
        ];

        return response()->json($response, 201);
    }

    // public function allCommentReplies($id){
    //     $commentReplies = CounselorReplyUser::where("comment_id", $id)->get();
    //     $commentRepliesCount = CounselorReplyUser::where("comment_id", $id)->get()->count();

    //     $response = [
    //         "commentReplies" => $commentReplies,
    //         "commentRepliesCount" => $commentRepliesCount,
    //     ];

    //     return response()->json($response, 200);
    // }


    public function counselorReplyUserComment($id){
        $counselorReply = CounselorReplyUser::with('counselor', 'userComment', 'userLikes', 'counselorLikes')
            ->withCount('userLikes', 'counselorLikes')
            ->findOrFail($id);

            $response = [
                'counnselorReply' => $counselorReply,
                    ];

                    return response()->json($response, 200);
    }

    public function counselorDeleteReply($id){
        $user = CounselorReplyUser::where('id', $id)->first();

        if($user->counselor_id != auth()->user()->id){
            $response = [
                "message" => "Unauthorized action"
            ];
            return response()->json($response, 401);
        }
        CounselorReplyUser::destroy($id);

        $response = [
            "message" => "deleted!"
        ];
        return response()->json($response, 200);
    }
}
