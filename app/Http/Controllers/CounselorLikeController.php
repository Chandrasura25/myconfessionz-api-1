<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CounselorLikePost;
use App\Models\CounselorLikeUserReply;
use App\Models\CounselorLikeUserComment;
use App\Models\CounselorLikeCounselorReply;
use App\Models\CounselorLikeCounselorComment;

class CounselorLikeController extends Controller
{
    public function counselorLikePost($id){

        $likedpost = CounselorLikePost::where('counselor_id', auth()->user()->id)
            ->where('post_id', $id)
            ->first();

        if($likedpost){
            CounselorLikePost::where('counselor_id', auth()->user()->id)
                ->where('post_id', $id)
                ->first()
                ->delete();

            $response = [
                "message" => "Post unliked!"
            ];

            return response()->json($response, 200);
        }

        $formFields = ([
            'post_id' => $id,
            'counselor_id' => auth()->user()->id
        ]);

        CounselorLikePost::create($formFields);

        $response = [
            "message" => "Post liked!"
        ];

        return response()->json($response, 201);
    }

    public function counselorLikeUserComment($pid, $cid){
        $ifliked = CounselorLikeUserComment::where('counselor_id', auth()->user()->id)
            ->where('post_id', $pid)
            ->where('user_comment_id', $cid)
            ->first();
        if($ifliked){
            CounselorLikeUserComment::where('counselor_id', auth()->user()->id)
            ->where('post_id', $pid)
            ->where('user_comment_id', $cid)
            ->first()
            ->delete();

            $response = [
                "message" => "Comment unliked!"
            ];

            return response()->json($response, 200);
        }
            CounselorLikeUserComment::Create([
                'post_id' => $pid,
                'user_comment_id' => $cid,
                'counselor_id' => auth()->user()->id,
            ]);

        $response = [
            "message" => "Comment liked!"
        ];

        return response()->json($response, 201);
    }


    public function counselorLikeCounselorComment($pid, $cid){
        $ifliked = CounselorLikeCounselorComment::where('counselor_id', auth()->user()->id)
            ->where('post_id', $pid)
            ->where('counselor_comment_id', $cid)
            ->first();
        if($ifliked){
            CounselorLikeCounselorComment::where('counselor_id', auth()->user()->id)
            ->where('post_id', $pid)
            ->where('counselor_comment_id', $cid)
            ->first()
            ->delete();

            $response = [
                "message" => "Comment unliked!"
            ];

            return response()->json($response, 200);
        }
            CounselorLikeCounselorComment::Create([
                'post_id' => $pid,
                'counselor_comment_id' => $cid,
                'counselor_id' => auth()->user()->id,
            ]);

        $response = [
            "message" => "Comment liked!"
        ];

        return response()->json($response, 201);
    }

    public function counselorLikeCounselorReply($pid, $cid, $rid){
        $ifLiked = CounselorLikeCounselorReply::where('counselor_id', auth()->user()->id)->
                                where('post_id', $pid)->
                                where('user_comment_id', $cid)->
                                where('counselor_reply_user_id', $rid)->
                                first();

        if($ifLiked){
            CounselorLikeCounselorReply::where('counselor_id', auth()->user()->id)
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
            CounselorLikeCounselorReply::Create([
                'post_id' => $pid,
                'user_comment_id' => $cid,
                'counselor_reply_user_id' => $rid,
                'counselor_id' => auth()->user()->id,
            ]);

            $response = [
                'message' => 'Liked!'
            ];
            return response()->json($response, 200);
        }
    }

    public function counselorLikeUserReply($pid, $cid, $rid){
        $ifLiked = CounselorLikeUserReply::where('counselor_id', auth()->user()->id)->
                                where('post_id', $pid)->
                                where('user_comment_id', $cid)->
                                where('user_reply_user_id', $rid)->
                                first();

        if($ifLiked){
            CounselorLikeUserReply::where('counselor_id', auth()->user()->id)
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
            CounselorLikeUserReply::Create([
                'post_id' => $pid,
                'user_comment_id' => $cid,
                'user_reply_user_id' => $rid,
                'counselor_id' => auth()->user()->id,
            ]);

            $response = [
                'message' => 'Liked!'
            ];
            return response()->json($response, 200);
        }
    }

    // function counselorLikeUserReplyUserLikes($pid, $cid, $rid){
    //     $likes = CounselorLikeUserReply::where('post_id', $pid)
    //         ->where('user_comment_id', $cid)
    //         ->where('user_reply_user_id', $rid)
    //         ->get();
    //     $response = [
    //         'message' => $likes
    //     ];
    //     return response()->json($response, 200);
    // }

    // function counselorLikeCounselorReplyUserLikes($pid, $cid, $rid){
    //     $likes = CounselorLikeCounselorReply::where('post_id', $pid)
    //         ->where('user_comment_id', $cid)
    //         ->where('counselor_reply_user_id', $rid)
    //         ->get();
    //     $response = [
    //         'message' => $likes
    //     ];
    //     return response()->json($response, 200);
    // }
}
