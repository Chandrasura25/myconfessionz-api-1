<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShareAction;
use App\Models\Post;

class ShareController extends Controller
{
    public function increaseShareCount(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $postId = $request->input('post_id');
            $post = Post::find($postId);

            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            ShareAction::updateOrCreate(
                [
                    'shareable_id' => $user->id,
                    'shareable_type' => get_class($user),
                    'post_id' => $postId
                ],
                ['count' => \DB::raw('count + 1')]
            );

            // Update the share_count in the posts table
            $post->increment('share_count');

            return response()->json(['message' => 'Share count increased successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
