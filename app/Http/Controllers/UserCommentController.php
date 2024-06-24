<?php

namespace App\Http\Controllers;

use App\Models\UserComment;
use Illuminate\Http\Request;
use App\Models\CounselorComment;

class UserCommentController extends Controller
{
    public function userComment(Request $request, $id){
        $request->validate([
            "comment" => 'required',
            // "category" => 'required'
        ]);

        $formFields = ([
            "post_id" => $id,
            "user_id" => auth()->user()->id,
            "user_comment" => $request->comment,
            // 'category' => $request->category,
        ]);

        UserComment::create($formFields);

        $response = [
            "message" => "Comment shared!"
        ];

        return response()->json($response, 201);
    }

//     public function allPostComments($id)
// {
//     $postComments = UserComment::with(['user', 'replies', 'counselorReplies', 'likes', 'counselorLikes'])
//         ->where('post_id', $id)
//         ->get();

//     $response = $postComments->map(function ($comment) {
//         $user = $comment->user;
//         $repliesCount = $comment->replies->count() + $comment->counselorReplies->count();
//         $likesCount = $comment->likes->count() + $comment->counselorLikes->count();

//         return [
//             'comment' => $comment->comment,
//             'userId' => $user->id,
//             'postId' => $comment->post_id,
//             'usercode' => $user->usercode,
//             'gender' => $user->gender,
//             'dob' => $user->dob,
//             'state' => $user->state,
//             'country' => $user->country,
//             'replies' => $comment->replies,
//             'counselorReplies' => $comment->counselorReplies,
//             'likes' => $comment->likes,
//             'counselorLikes' => $comment->counselorLikes,
//             'allRepliesCount' => $repliesCount,
//             'overallLikesCount' => $likesCount,
//             'createdAt' => $comment->created_at,
//             'updatedAt' => $comment->updated_at,
//             'commentId' => $comment->id
//         ];
//     });

//     return response()->json($response, 200);
// }

   public function singleUserComment($id){
        $userComment = UserComment::with('user', 'post', 'userReplies', 'counselorReplies')
            ->withCount('userLikes', 'counselorLikes', 'userReplies', 'counselorReplies')
            ->findOrFail($id);

        // Calculate the total number of replies
        $allRepliesCount = $userComment->user_replies_count + $userComment->counselor_replies_count;

        // Calculate the total number of likes
        $allCommentLikes = $userComment->user_likes_count + $userComment->counselor_likes_count;

        $response = [
            "user" => $userComment->user,
            "userComment" => $userComment,
            "userReplies" => $userComment->userReplies,
            "counselorReplies" => $userComment->counselorReplies,
            "userLikes" => $userComment->userLikes,
            "counselorLikes" => $userComment->counselorLikes,
            "allLikes" => $allCommentLikes,
            "allReplies" => $allRepliesCount
        ];

        return response()->json($response, 200);
    }

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
                'allComments'=>$allReplies
            ];

            return response()->json($response, 200);

            }

    public function deleteUserComment($id){
        $user = UserComment::where('id', $id)->first();

        if($user->user_id != auth()->user()->id){
            $response = [
                "message" => "Unauthorized action"
            ];
            return response()->json($response, 401);
        }
        UserComment::destroy($id);

        $response = [
            "message" => "Comment deleted!"
        ];
        return response()->json($response, 200);
    }
}
