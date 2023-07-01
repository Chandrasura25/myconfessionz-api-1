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
        Schema::create('counselor_reply_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('counselor_id');
            $table->unsignedBigInteger('user_comment_id');
            $table->longText('counselor_reply');
            $table->foreign('counselor_id')->references('id')->on('counselors')->onDelete('cascade');
            $table->foreign('user_comment_id')->references('id')->on('user_comments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselor_reply_users');
    }
};
