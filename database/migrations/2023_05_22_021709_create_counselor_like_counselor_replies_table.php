<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('counselor_like_counselor_replies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('counselor_id');
            $table->unsignedBigInteger('user_comment_id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('counselor_reply_user_id');
            $table->foreign('counselor_id')->references('id')->on('counselors')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('user_comment_id')->references('id')->on('user_comments')->onDelete('cascade');
            $table->foreign('counselor_reply_user_id')->references('id')->on('counselor_reply_users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselor_like_counselor_replies');
    }
};
