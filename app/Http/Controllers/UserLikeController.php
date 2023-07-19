<?php

namespace App\Http\Controllers;

use App\Models\UserLikePost;
use Illuminate\Http\Request;
use App\Models\UserLikeUserReply;
use App\Models\UserLikeUserComment;
use App\Models\UserLikeCounselorReply;
use App\Models\UserLikeCounselorComment;

class UserLikeController extends Controller
{
    public function userLikePost($id){
        $userLikedPost = UserLikePost::where('user_id', auth()->user()->id)->where('post_id', $id)->first();

        if($userLikedPost){
            UserLikePost::where('user_id', auth()->user()->id)->where('post_id', $id)->first()->delete();

            $response = [
                "message" => "Post unliked!"
            ];

            return response()->json($response, 200);
        }

        $formFields = ([
            'post_id' => $id,
            'user_id' => auth()->user()->id
        ]);

        UserLikePost::create($formFields);

        $response = [
            "message" => "Post liked!"
        ];

        return response()->json($response, 201);
    }

    public function userLikeUserComment($postId, $commentId){
        $userLikedComment = UserLikeUserComment::where('user_id', auth()->user()->id)
            ->where('post_id', $postId)
            ->where('user_comment_id', $commentId)
            ->first();

        if($userLikedComment){
            UserLikeUserComment::where('user_id', auth()->user()->id)
                ->where('post_id', $postId)
                ->where('user_comment_id', $commentId)
                ->first()
                ->delete();

            $response = [
                "message" => "Comment unliked!"
            ];

            return response()->json($response, 200);
        }
            UserLikeUserComment::Create([
                'post_id' => $postId,
                'user_comment_id' => $commentId,
                'user_id' => auth()->user()->id,
            ]);

        $response = [
            "message" => "Comment liked!"
        ];

        return response()->json($response, 201);
    }

        public function userLikeCounselorComment($postId, $commentId){
        $userLikedComment = UserLikeCounselorComment::where('user_id', auth()->user()->id)
            ->where('post_id', $postId)
            ->where('counselor_comment_id', $commentId)
            ->first();

        if($userLikedComment){
            UserLikeCounselorComment::where('user_id', auth()->user()->id)
                ->where('post_id', $postId)
                ->where('counselor_comment_id', $commentId)
                ->first()
                ->delete();

            $response = [
                "message" => "Comment unliked!"
            ];

            return response()->json($response, 200);
        }
            UserLikeCounselorComment::Create([
                'post_id' => $postId,
                'counselor_comment_id' => $commentId,
                'user_id' => auth()->user()->id,
            ]);

        $response = [
            "message" => "Comment liked!"
        ];

        return response()->json($response, 201);
        }

    public function userLikeUserReply($pid, $cid, $rid){
        $userLikedReply = UserLikeUserReply::where('user_id', auth()->user()->id)->
                                where('post_id', $pid)->
                                where('user_comment_id', $cid)->
                                where('user_reply_user_id', $rid)->
                                first();

        if($userLikedReply){
            UserLikeUserReply::where('user_id', auth()->user()->id)
                ->where('post_id', $pid)
                ->where('user_comment_id', $cid)
                ->where('user_reply_user_id', $rid)
                ->first()
                ->delete();

            $response = [
                'message' => 'Unliked!'
            ];
            return response()->json($response, 200);
        }
        else{
            UserLikeUserReply::Create([
                'post_id' => $pid,
                'user_comment_id' => $cid,
                'user_reply_user_id' => $rid,
                'user_id' => auth()->user()->id,
            ]);

            $response = [
                'message' => 'Liked!'
            ];
            return response()->json($response, 200);
        }
    }

    public function userLikeCounselorReply($pid, $cid, $rid){
        $userLikedReply = UserLikeCounselorReply::where('user_id', auth()->user()->id)->
                                where('post_id', $pid)->
                                where('user_comment_id', $cid)->
                                where('counselor_reply_user_id', $rid)->
                                first();

        if($userLikedReply){
            UserLikeCounselorReply::where('user_id', auth()->user()->id)
                ->where('post_id', $pid)
                ->where('user_comment_id', $cid)
                ->where('counselor_reply_user_id', $rid)
                ->first()
                ->delete();

            $response = [
                'message' => 'Unliked!'
            ];
            return response()->json($response, 200);
        }
        else{
            UserLikeCounselorReply::Create([
                'post_id' => $pid,
                'user_comment_id' => $cid,
                'counselor_reply_user_id' => $rid,
                'user_id' => auth()->user()->id,
            ]);

            $response = [
                'message' => 'Liked!'
            ];
            return response()->json($response, 200);
        }
    }


    // function userLikeUserReplyUserLikes($pid, $cid, $rid){
    //     $likes = UserLikeUserReply::where('post_id', $pid)
    //         ->where('user_comment_id', $cid)
    //         ->where('user_reply_user_id', $rid)
    //         ->get();
    //     $response = [
    //         'message' => $likes
    //     ];
    //     return response()->json($response, 200);
    // }

    // function userLikeUserReplyCounselorLikes($pid, $cid, $rid){
    //     $likes = UserLikeCounselorReply::where('post_id', $pid)
    //         ->where('user_comment_id', $cid)
    //         ->where('counselor_reply_user_id', $rid)
    //         ->get();
    //     $response = [
    //         'message' => $likes
    //     ];
    //     return response()->json($response, 200);
    // }
}

