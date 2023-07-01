<?php

namespace App\Http\Controllers;

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

    public function allCommentReplies($id){
        $commentReplies = UserReplyUser::where("user_comment_id", $id)->get();
        $commentRepliesCount = UserReplyUser::where("user_comment_id", $id)->get()->count();

        $response = [
            "commentReplies" => $commentReplies,
            "commentRepliesCount" => $commentRepliesCount,
        ];

        return response()->json($response, 200);
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
