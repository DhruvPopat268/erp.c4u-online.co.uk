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
        Schema::create('work_around_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->string('operating_centres')->nullable();
            $table->string('vehicle_id')->nullable();
            $table->string('speedo_odometer')->nullable();
            $table->string('fuel_level')->nullable();
            $table->string('adblue_level')->nullable();
            $table->string('step')->nullable();
            $table->timestamps();

            $table->foreign('profile_id')->references('id')->on('work_around_profiles')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_around_stores');
    }
};
