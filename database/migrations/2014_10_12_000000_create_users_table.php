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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('usercode')->unique();
            $table->string('gender');
            $table->date('dob');
            $table->string('country');
            $table->string('state');
            $table->string('password');
            $table->bigInteger('balance')->default(0);
            $table->string('recovery_question1');
            $table->string('answer1');
            $table->string('recovery_question2');
            $table->string('answer2');
            $table->string('recovery_question3');
            $table->string('answer3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
