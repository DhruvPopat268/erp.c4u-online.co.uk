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
        Schema::create('fleet_planner_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fleet_planner_id');
            $table->date('next_reminder_date');
            $table->string('status');
            $table->timestamps();

            $table->foreign('fleet_planner_id')->references('id')->on('fleets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_planner_reminders');
    }
};
