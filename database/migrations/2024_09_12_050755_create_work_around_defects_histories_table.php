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
        Schema::create('work_around_defects_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workaround_question_answer_id');
            $table->text('reason')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

           // Manually define a shorter name for the foreign key constraint
    $table->foreign('workaround_question_answer_id', 'wa_defects_hist_qa_id_fk')
    ->references('id')
    ->on('work_around_question_answer_stores')
    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_around_defects_histories');
    }
};
