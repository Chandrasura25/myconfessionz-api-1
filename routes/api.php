<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthCounselorController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CounsellorChatController;
use App\Http\Controllers\CounselorCommentController;
use App\Http\Controllers\CounselorLikeController;
use App\Http\Controllers\CounselorManagementController;
use App\Http\Controllers\CounselorReplyController;
use App\Http\Controllers\CounselorSearchController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserCommentController;
use App\Http\Controllers\UserLikeController;
use App\Http\Controllers\UserReplyController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\CounselorPaymentController;
use Illuminate\Support\Facades\Broadcast;
// use Illuminate\Support\Facades\File;

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
Broadcast::routes(['middleware' => ['auth:sanctum']]);
// Private Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account/{id}', [AuthController::class, 'deleteAccount']);
    Route::get('/user', [AuthController::class, 'getUser']);
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
    Route::get('/counselor-single-comment/{commentId}', [UserCommentController::class, 'singleCounselorComment']);

    // REPLY ROUTES
    Route::post('/user-reply-user/{commentId}', [UserReplyController::class, 'userReplyUser']);
    Route::delete('/user-delete-reply/{id}', [UserReplyController::class, 'userDeleteReply']);

    // LIKE ROUTES
    Route::post('/user-like-post/{postId}', [UserLikeController::class, 'userLikePost']);
    Route::post('/user-like-user-comment/{postId}/{commentId}', [UserLikeController::class, 'userLikeUserComment']);
    Route::post('/user-like-counselor-comment/{postId}/{commentId}', [UserLikeController::class, 'userLikeCounselorComment']);
    Route::post('/user-like-user-reply/{postId}/{commentIid}/{replyId}', [UserLikeController::class, 'userLikeUserReply']);
    Route::post('/user-like-counselor-reply/{postId}/{commentIid}/{replyId}', [UserLikeController::class, 'userLikeCounselorReply']);

    // Route::get('/user-like-user-reply-user-likes/{pid}/{cid}/{rid}', [UserLikeController::class, 'userLikeUserReplyUserLikes']);
    // Route::get('/user-like-counselor-reply-user-likes/{pid}/{cid}/{rid}', [UserLikeController::class, 'userLikeCounselorReplyUserLikes']);
    Route::get('/user-comment-replies/{id}', [UserReplyController::class, 'userReplyUserComment']);
    Route::get('/counselor-comment-replies/{id}', [UserReplyController::class, 'counselorReplyUserComment']);

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

    Route::get('/verify-payment/{reference}', [PaymentController::class, 'verifyPayment'])->name('verify.payment');
    Route::get('/balance', [PaymentController::class, 'getBalance']);
    Route::post('/verify-success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    //CREATE SESSION
    Route::post('/create-session/{counselor_id}',[SessionController::class,'processSession']);
    Route::get('/check-session/{counselor_id}',[SessionController::class,'checkSession']);
    Route::post('/end-session/{counselor_id}',[SessionController::class,'endSession']);
    Route::get('/get-user-sessions',[SessionController::class,'getAllActiveSessions']);
    
    
    //CHAT SYSTEM
    Route::post('/initiate-conversation', [ChatController::class, 'initiateConversation'])
        ->name('conversation.initiate');
    Route::get('/conversations', [ChatController::class, 'getUserConversations'])->name('users.conversations');
    Route::get('/conversations/{conversationId}/messages', [ChatController::class, 'getMessages'])->name('users.conversations.messages');
    Route::delete('/delete-conversations/{id}', [ChatController::class, 'deleteConversation']);  
    Route::delete('/delete-messages/{id}', [ChatController::class, 'deleteMessage']);  
    
    Route::post('/messages', [ChatController::class, 'sendMessage'])->name('users.messages.send');
    Route::get('/messages/{id}/mark-as-read', [ChatController::class, 'markAsRead'])->name('user.messages.mark-as-read');
    
    // Share function
    Route::post('/share', [ShareController::class, 'increaseShareCount']);
});

// COUNSELLORS

// Public Routes
Route::post('/register-counselor', [AuthCounselorController::class, 'registerCounselor']);
Route::post('/login-counselor', [AuthCounselorController::class, 'loginCounselor']);
Route::post('/counselor-password-reset-request', [AuthCounselorController::class, 'counselorPasswordResetRequest']);
Route::post('/counselor-password-recovery-answer', [AuthCounselorController::class, 'counselorPasswordRecoveryAnswer']);
Route::post('/counselor-reset-password', [AuthCounselorController::class, 'counselorPasswordReset']);

// Private Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout-counselor', [AuthCounselorController::class, 'logoutCounselor']);
    // Route::get('counselors/{image}', [AuthCounselorController::class, 'getImage']);
     Route::get('/allusers', [AuthCounselorController::class, 'getAllUsers']);
     Route::get('/user/{id}', [AuthCounselorController::class, 'getUser']);
     Route::get('/counselors/{image}', [AuthCounselorController::class, 'getImage']);
     Route::get('/get-single-counselor/{id}', [AuthCounselorController::class, 'singleCounselor']);
     Route::get('/all-posts-explore', [PostController::class, 'allPostsExplore']);
     Route::get('/all-posts-home', [PostController::class, 'allPostsHome']);
     Route::get('/single-post/{id}', [PostController::class, 'singlePost']);

    // COMMENT
    Route::post('/counselor-comment/{id}', [CounselorCommentController::class, 'counselorComment']);
    Route::delete('/counselor-delete-comment/{id}', [CounselorCommentController::class, 'counselorDeleteComment']);
    Route::get('/user-single-comment/{commentId}', [CounselorCommentController::class, 'singleUserComment']);
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

    // Route::get('/counselor-like-user-reply-user-likes/{pid}/{cid}/{rid}', [CounselorLikeController::class, 'counselorLikeUserReplyUserLikes']);
    // Route::get('/counselor-like-counselor-reply-user-likes/{pid}/{cid}/{rid}', [CounselorLikeController::class, 'counselorLikeCounselorReplyUserLikes']);
     Route::get('/user-comment-replies/{id}', [CounselorReplyController::class, 'userReplyUserComment']);
    Route::get('/counselor-comment-replies/{id}', [CounselorReplyController::class, 'counselorReplyUserComment']);

    // Route::get('/all-post-likes-counselor/{id}', [CounselorLikeReplyController::class, 'allPostLikes']);
    // Route::get('/all-comment-likes-counselor/{id}', [CounselorLikeReplyController::class, 'allCommentLikes']);
    // Route::get('/all-reply-likes-counselor/{id}', [CounselorLikeReplyController::class, 'allReplyLikes']);

    // MANAGE COUNSELOR ROUTES
    Route::delete('/delete-counselor-account/{id}', [CounselorManagementController::class, 'deleteAccount']);

    //END SESSION\
    Route::get('/check-session/{userId}',[CounsellorChatController::class,'checkSession']);
    Route::delete('/end-session',[CounsellorChatController::class,'endSession']);
    Route::get('/get-counselor-sessions',[CounsellorChatController::class,'getAllActiveUsers']);
    

    //CHAT SYSTEM
    Route::get('/counselor-conversations', [CounsellorChatController::class, 'getConversations'])->name('counselors.conversations');
    Route::get('/counselor-conversations/{conversationId}/messages', [CounsellorChatController::class, 'getMessages'])->name('counselors.conversations.messages');
    Route::post('/counselor-messages', [CounsellorChatController::class, 'sendMessage'])->name('counselor.messages.send');
    Route::get('/counselor-messages/{id}/mark-as-read', [CounsellorChatController::class, 'markAsRead'])->name('counselors.messages.mark-as-read');
    Route::delete('/delete-counselor-messages/{id}', [CounsellorChatController::class, 'deleteMessage']); 

     // PAYMENT
    Route::post('/verify-account', [CounselorPaymentController::class, 'verifyAccount']);
    Route::post('/initiate-transfer', [CounselorPaymentController::class, 'initiateTransfer']);
    Route::post('/finalize-payment', [CounselorPaymentController::class, 'finalizePayment']);
    Route::get('/verify-payment/{reference}', [CounselorPaymentController::class, 'verifyPayment']);

    Route::get('counselors/{image}', function ($image) {
        $imagePath = 'counselors/' . $image; // Replace with the actual path to your image file

        // Check if the image file exists
        if (File::exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));

            return response()->json(['image' => $imageData]);
        } else {
            return response()->json(['error' => 'Image not found'], 404);
        }
    });
});
