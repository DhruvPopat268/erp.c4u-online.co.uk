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
        Schema::create('fleet_file_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fleet_planner_reminder_id');
            $table->string('file_path');
            $table->timestamps();

            $table->foreign('fleet_planner_reminder_id')->references('id')->on('fleet_planner_reminders')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_file_uploads');
    }
};
