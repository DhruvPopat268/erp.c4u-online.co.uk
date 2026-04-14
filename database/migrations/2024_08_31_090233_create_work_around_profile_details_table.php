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
        Schema::create('work_around_profile_details', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('vehicle_id')->nullable();
            $table->string('work_around_question_id')->nullable();
            $table->string('work_around_profile_id')->nullable();
            $table->string('reason')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_around_profile_details');
    }
};
