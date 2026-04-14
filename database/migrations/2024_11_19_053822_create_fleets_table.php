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
        Schema::create('fleets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->string('planner_type');
            $table->string('start_date');
            $table->string('end_date');
            $table->string('time_interval');
            $table->string('created_by');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('company_details')->onDelete('cascade');
        $table->foreign('vehicle_id')->references('id')->on('vehicle_details')->onDelete('cascade');
        });



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleets');
    }
};
