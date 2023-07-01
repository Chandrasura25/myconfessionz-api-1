<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserReplyController;
use App\Http\Controllers\UserLikeController;
use App\Http\Controllers\UserCommentController;
use App\Http\Controllers\AuthCounselorController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CounsellorChatController;
use App\Http\Controllers\CounselorReplyController;
use App\Http\Controllers\CounselorSearchController;
use App\Http\Controllers\CounselorCommentController;
use App\Http\Controllers\CounselorLikeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CounselorManagementController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



// USERS

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password-reset-request', [AuthController::class, 'passwordResetRequest']);
Route::post('/password-recovery-answer', [AuthController::class, 'passwordRecoveryAnswer']);
Route::post('/reset-password', [AuthController::class, 'passwordReset']);

// Private Routes
Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account/{id}', [AuthController::class, 'deleteAccount']);

    // POST
    Route::post('/create-post', [PostController::class, 'createPost']);
    Route::get('/single-post/{id}', [PostController::class, 'singlePost']);
    Route::get('/all-posts-home', [PostController::class, 'allPostsHome']);
    Route::get('/all-posts-explore', [PostController::class, 'allPostsExplore']);
    Route::delete('/delete-post/{id}', [PostController::class, 'deletePost']);

    // COMMENT
    Route::post('/user-comment/{postId}', [UserCommentController::class, 'userComment']);
    Route::delete('/user-delete-comment/{commentId}', [UserCommentController::class, 'deleteUserComment']);
    Route::get('/user-single-comment/{commentId}', [UserCommentController::class, 'singleUserComment']);
    // Route::get('/all-post-comments/{id}', [UserCommentController::class, 'allPostComments']);

    // REPLY ROUTES
    Route::post('/user-reply-user/{commentId}', [UserReplyController::class, 'userReplyUser']);
    Route::delete('/user-delete-reply/{id}', [UserReplyController::class, 'userDeleteReply']);
    // Route::get('/all-comment-replies/{id}', [ReplyController::class, 'allCommentReplies']);

    // LIKE ROUTES
    Route::post('/user-like-post/{postId}', [UserLikeController::class, 'userLikePost']);
    Route::post('/user-like-user-comment/{postId}/{commentId}', [UserLikeController::class, 'userLikeUserComment']);
    Route::post('/user-like-counselor-comment/{postId}/{commentId}', [UserLikeController::class, 'userLikeCounselorComment']);
    Route::post('/user-like-user-reply/{postId}/{commentIid}/{replyId}', [UserLikeController::class, 'userLikeUserReply']);
    Route::post('/user-like-counselor-reply/{postId}/{commentIid}/{replyId}', [UserLikeController::class, 'userLikeCounselorReply']);

    // Route::get('/all-post-likes/{id}', [LikeReplyController::class, 'allPostLikes']);
    // Route::get('/all-comment-likes/{id}', [LikeReplyController::class, 'allCommentLikes']);
    // Route::get('/all-reply-likes/{id}', [LikeReplyController::class, 'allReplyLikes']);


    //SEARCH COUNSELOR ROUTES
    Route::get('/search-counselor-username', [CounselorSearchController::class, 'searchUsername']);
    Route::get('/search-counselor-field', [CounselorSearchController::class, 'searchField']);

    // MANAGE COUNSELOR ROUTES
    Route::get('/get-single-counselor/{id}', [CounselorManagementController::class, 'singleCounselor']);
    Route::get('/counselors-by-field/{field}', [CounselorManagementController::class, 'counselorsByField']);
    Route::get('/all-counselors', [CounselorManagementController::class, 'allCounselors']);

    // REVIEWS ROUTES
    Route::post('/user-create-review/{counselorId}', [ReviewController::class, 'createReview']);
    Route::delete('/delete-review/{reviewId}', [ReviewController::class, 'deleteReview']);
    Route::get('/single-counselor-reviews/{counselorId}', [ReviewController::class, 'singleCounselorReviews']);

    //CHAT SYSTEM
    Route::post('/initiate-conversation', [ChatController::class, 'initiateConversation'])
    ->name('conversation.initiate');
    Route::get('/conversations', [ChatController::class, 'getUserConversations'])->name('users.conversations');
    Route::get('/conversations/{conversationId}/messages', [ChatController::class, 'getMessages'])->name('users.conversations.messages');
    Route::post('/messages/{id}/mark-as-read', [ChatController::class, 'markAsRead'])->name('user.messages.mark-as-read');
});






// COUNSELLORS

// Public Routes
Route::post('/register-counselor', [AuthCounselorController::class, 'registerCounselor']);
Route::post('/login-counselor', [AuthCounselorController::class, 'loginCounselor']);
Route::post('/counselor-password-reset-request', [AuthCounselorController::class, 'counselorPasswordResetRequest']);
Route::post('/counselor-password-recovery-answer', [AuthCounselorController::class, 'counselorPasswordRecoveryAnswer']);
Route::post('/counselor-reset-password', [AuthCounselorController::class, 'counselorPasswordReset']);

// Private Routes
Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/logout-counselor', [AuthCounselorController::class, 'logoutCounselor']);

    // COMMENT
    Route::post('/counselor-comment/{id}', [CounselorCommentController::class, 'counselorComment']);
    Route::delete('/counselor-delete-comment/{id}', [CounselorCommentController::class, 'counselorDeleteComment']);
    Route::get('/counselor-single-comment/{commentId}', [CounselorCommentController::class, 'singleCounselorComment']);
    // Route::get('/all-post-comments-counselor/{id}', [CounselorCommentController::class, 'allPostComments']);

    // REPLY ROUTES
    Route::post('/counselor-reply-user/{commentId}', [CounselorReplyController::class, 'counselorReplyUser']);
    Route::delete('/counselor-delete-reply/{id}', [CounselorReplyController::class, 'counselorDeleteReply']);
    // Route::get('/all-comment-replies-counselor/{id}', [CounselorreplyController::class, 'allCommentReplies']);

    // LIKE ROUTES
    Route::post('/counselor-like-post/{id}', [CounselorLikeController::class, 'counselorLikePost']);
    Route::post('/counselor-like-counselor-comment/{pid}/{cid}', [CounselorLikeController::class, 'counselorLikeCounselorComment']);
    Route::post('/counselor-like-user-comment/{pid}/{cid}', [CounselorLikeController::class, 'counselorLikeUserComment']);
    Route::post('/counselor-like-counselor-reply/{pid}/{cid}/{rid}', [CounselorLikeController::class, 'counselorLikeCounselorReply']);
    Route::post('/counselor-like-user-reply/{pid}/{cid}/{rid}', [CounselorLikeController::class, 'counselorLikeUserReply']);

    // Route::get('/all-post-likes-counselor/{id}', [CounselorLikeReplyController::class, 'allPostLikes']);
    // Route::get('/all-comment-likes-counselor/{id}', [CounselorLikeReplyController::class, 'allCommentLikes']);
    // Route::get('/all-reply-likes-counselor/{id}', [CounselorLikeReplyController::class, 'allReplyLikes']);

    // MANAGE COUNSELOR ROUTES
    Route::delete('/delete-counselor-account/{id}', [CounselorManagementController::class, 'deleteAccount']);

   //CHAT SYSTEM
    Route::get('/conversations', [CounselorChatController::class, 'getConversations'])->name('counselors.conversations');
    Route::get('/conversations/{conversationId}/messages', [CounselorChatController::class, 'getMessages'])->name('counselors.conversations.messages');
    Route::post('/messages', [CounsellorChatController::class, 'sendMessage'])->name('counselor.messages.send');
    Route::post('/messages/{id}/mark-as-read', [CounselorChatController::class, 'markAsRead'])->name('counselors.messages.mark-as-read');
});
