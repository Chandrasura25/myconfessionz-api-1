<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CounselorComment;

class CounselorCommentController extends Controller
{
    public function counselorComment(Request $request, $id){
        $request->validate([
            "comment" => 'required'
        ]);

        $formFields = ([
            "post_id" => $id,
            "counselor_id" => auth()->user()->id,
            "counselor_comment" => $request->comment,
            'category' => $request->category,
        ]);

        CounselorComment::create($formFields);

        $response = [
            "message" => "Advice shared!"
        ];

        return response()->json($response, 201);
    }

//         public function allPostComments($id)
//         {
//             $postComments = CounselorComment::with(['counselor', 'likes', 'counselorLikes'])
//             ->where('post_id', $id)->get();
//             $response = $postComments->map(function ($comment) {
//             $counselor = $comment->counselor;
//             $likesCount = $comment->likes->count() + $comment->counselorLikes->count();

//         return [
//             'comment' => $comment->comment,
//             'userId' => $counselor->id,
//             'postId' => $comment->post_id,
//             'username' => $counselor->username,
//             'firstName' => $counselor->first_name,
//             'lastName' => $counselor->last_name,
//             'image' => $counselor->image,
//             'gender' => $counselor->gender,
//             'dob' => $counselor->dob,
//             'state' => $counselor->state,
//             'country' => $counselor->country,
//             'likes' => $comment->likes,
//             'counselorLikes' => $comment->counselorLikes,
//             'overallLikesCount' => $likesCount,
//             'createdAt' => $comment->created_at,
//             'updatedAt' => $comment->updated_at,
//             'commentId' => $comment->id
//         ];
//     });

//     return response()->json($response, 200);
// }


        public function singleCounselorComment($id){
            $counselorComment = CounselorComment::with('counselor', 'post')
                ->withCount('userLikes', 'counselorLikes')
                ->findOrFail($id);

            $allReplies = $counselorComment->user_comments_count + $counselorComment->counselor_comments_count;
            $allCommentLikes = $counselorComment->user_likes_count + $counselorComment->counselor_likes_count;

            $response = [
                "counselor" => $counselorComment->counselor,
                "counselorComment" => $counselorComment,
                "userLikes" => $counselorComment->userLikes,
                "counselorLikes" => $counselorComment->counselorLikes,
                "allLikes" => $allCommentLikes,
            ];

            return response()->json($response, 200);

            }
    public function counselorDeleteComment($id){
        $counselor = CounselorComment::where('id', $id)->first();

        if($counselor->counselor_id != auth()->user()->id){
            $response = [
                "message" => "Unauthorized action"
            ];
            return response()->json($response, 401);
        }
        CounselorComment::destroy($id);

        $response = [
            "message" => "Comment deleted!"
        ];
        return response()->json($response, 200);
    }
}
