<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation; 
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/


Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Retrieve the conversation based on $conversationId
    $conversation = Conversation::findOrFail($conversationId);

    // Check if the user is authorized to access this conversation
    // Example logic: Check if the user is part of this conversation
    // You can adjust this logic based on your application's requirements
    if ($conversation->user_id === $user->id || $conversation->counselor_id === $user->id) {
        return ['id' => $user->id, 'name' => $user->name]; // Optionally, return user information
    }

    return false; // Return false if user is not authorized
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
