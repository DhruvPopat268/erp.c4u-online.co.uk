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
        Schema::create('fleet_tyre_depths', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('fleet_planner_reminder_id');
            $table->unsignedBigInteger('vehicle_id');
        $table->float('ns_depth_1')->nullable();
        $table->float('ns_depth_2')->nullable();
        $table->float('ns_depth_3')->nullable();
        $table->float('ns_depth_4')->nullable();
        $table->float('ns_depth_5')->nullable();
        $table->float('ns_depth_6')->nullable();
        $table->float('os_depth_1')->nullable();
        $table->float('os_depth_2')->nullable();
        $table->float('os_depth_3')->nullable();
        $table->float('os_depth_4')->nullable();
        $table->float('os_depth_5')->nullable();
        $table->float('os_depth_6')->nullable();
            $table->timestamps();

            $table->foreign('fleet_planner_reminder_id')->references('id')->on('fleet_planner_reminders')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicle_details')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_tyre_depths');
    }
};
