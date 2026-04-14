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
        Schema::create('work_around_question_answer_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_details_id'); // This should match the WorkAroundProfileDetails model ID
            $table->unsignedBigInteger('question_id');
            $table->string('status');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('profile_details_id')->references('id')->on('work_around_profile_details')->onDelete('cascade');
        $table->foreign('question_id')->references('id')->on('work_around_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_around_question_answer_stores');
    }
};
