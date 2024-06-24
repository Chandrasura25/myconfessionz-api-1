<?php

namespace App\Http\Controllers;

use App\Models\UserComment;
use App\Models\CounselorComment;
use App\Models\CounselorLikePost;
use App\Models\UserLikePost;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function createPost(Request $request){
        $request->validate([
            'post' => 'required',
            'category' => 'required|string'
        ]);

        $formFields = ([
            'user_id' => auth()->user()->id,
            'post' => $request->post,
            'category' => $request->category
        ]);

        $post = Post::create($formFields);

        $response = [
            "message" => "Confession made successfully",
            "data" => $post,

        ];

        return response()->json($response, 201);
    }

    // public function singlePost($id){
    //     $post = Post::find($id);
    //     $user = $post->user;
    //     $userComments = UserComment::where('post_id', $id)->latest()->get();
    //     $counselorComments = CounselorComment::where('post_id', $id)->latest()->get();
    //     $userPostLikes = UserLikePost::where('post_id', $id)->get();
    //     $counselorPostLikes = CounselorLikePost::where('post_id', $id)->get();
    //     $allComments = UserComment::where('post_id', $id)->latest()->get()->count() + CounselorComment::where('post_id', $id)->latest()->get()->count();
    //     $allPostLikes = $userPostLikes->count() + $counselorPostLikes->count();
    //     $response = [
    //         "user" => $user,
    //         "post" => $post,
    //         "userComments" => $userComments,
    //         "counselorComments" => $counselorComments,
    //         "userLikes" => $userPostLikes,
    //         "counselorLikes" => $counselorPostLikes,
    //         "allLikes" => $allPostLikes,
    //         "allComments" => $allComments
    //     ];

    //     return response()->json($response, 200);
    // }


    // public function singlePost($id)
    // {
    //     $post = Post::with('user', 'userComments', 'counselorComments')
    //         ->withCount('userComments', 'counselorComments', 'userLikes', 'counselorLikes')
    //         ->findOrFail($id);

    //     $allComments = $post->user_comments_count + $post->counselor_comments_count;
    //     $allPostLikes = $post->user_likes_count + $post->counselor_likes_count;

    //     $response = [
    //         "user" => $post->user,
    //         "post" => $post,
    //         "userComments" => $post->userComments,
    //         "counselorComments" => $post->counselorComments,
    //         "userLikes" => $post->userLikes,
    //         "counselorLikes" => $post->counselorLikes,
    //         "allComments" => $allComments,
    //         "allLikes" => $allPostLikes
    //     ];

    //     return response()->json($response, 200);
    // }
    public function singlePost($id)
    {
        $post = Post::with([
            'user', 
            'userComments.user', // Eager load the user info with user comments
            'counselorComments.counselor' // Eager load the counselor info with counselor comments
        ])
        ->withCount('userComments', 'counselorComments', 'userLikes', 'counselorLikes')
        ->findOrFail($id);

        $allComments = $post->user_comments_count + $post->counselor_comments_count;
        $allPostLikes = $post->user_likes_count + $post->counselor_likes_count;

        $response = [
            "user" => $post->user,
            "post" => $post,
            "userComments" => $post->userComments->map(function($comment) {
                return [
                    'comment' => $comment,
                    'user' => $comment->user // Include user info
                ];
            }),
            "counselorComments" => $post->counselorComments->map(function($comment) {
                return [
                    'comment' => $comment,
                    'counselor' => $comment->counselor // Include counselor info
                ];
            }),
            "userLikes" => $post->userLikes,
            "counselorLikes" => $post->counselorLikes,
            "allComments" => $allComments,
            "allLikes" => $allPostLikes
        ];

        return response()->json($response, 200);
    }



    public function allPostsHome()
    {
        $posts = Post::with(['user:id,usercode,gender,dob,state,country', 'userComments', 'counselorComments', 'userLikes', 'counselorLikes'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        $response = [];

        foreach ($posts as $post) {
            $responseData = [
                "post" => $post->post,
                "userId" => $post->user->id,
                "usercode" => $post->user->usercode,
                "gender" => $post->user->gender,
                "dob" => $post->user->dob,
                "state" => $post->user->state,
                "country" => $post->user->country,
                "userComments" => $post->userComments,
                "category" => $post->category,
                "counselorComments" => $post->counselorComments,
                "userLikes" => $post->userLikes,
                "counselorLikes" => $post->counselorLikes,
                "allCommentsCount" => $post->userComments->count() + $post->counselorComments->count(),
                "overallLikesCount" => $post->userLikes->count() + $post->counselorLikes->count(),
                "createdAt" => $post->created_at,
                "updatedAt" => $post->updated_at,
                "postId" => $post->id
            ];

            $response[] = $responseData; // Append the response data to the array
        }

        return response()->json($response, 200);
    }


    public function allPostsExplore(){
        $posts = Post::with(['user:id,usercode,gender,dob,state,country', 'userComments', 'counselorComments', 'userLikes', 'counselorLikes'])
        ->inRandomOrder()->limit(5)->get();
        $response = [];

        foreach ($posts as $post) {
            $responseData = [
                "post" => $post->post,
                "userId" => $post->user->id,
                "usercode" => $post->user->usercode,
                "gender" => $post->user->gender,
                "dob" => $post->user->dob,
                "state" => $post->user->state,
                "country" => $post->user->country,
                "userComments" => $post->userComments,
                "category" => $post->category,
                "counselorComments" => $post->counselorComments,
                "userLikes" => $post->userLikes,
                "counselorLikes" => $post->counselorLikes,
                "allCommentsCount" => $post->userComments->count() + $post->counselorComments->count(),
                "overallLikesCount" => $post->userLikes->count() + $post->counselorLikes->count(),
                "createdAt" => $post->created_at,
                "updatedAt" => $post->updated_at,
                "postId" => $post->id
            ];

            $response[] = $responseData; // Append the response data to the array
        }

        return response()->json($response, 200);
    }

    public function deletePost($id){
        $user = Post::where('id', $id)->first();
        if($user->user_id != auth()->id()){
            $response = [
                "message" => "Unauthorized action"
            ];
            return response()->json($response, 401);
        }
        Post::destroy($id);

        $response = [
            "message" => "Confession deleted!"
        ];
        return response()->json($response, 200);
    }
}
