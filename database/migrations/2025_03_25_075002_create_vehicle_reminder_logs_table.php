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
        Schema::create('vehicle_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('company_id');
            $table->string('registration_number');
            $table->enum('status')->default('Pending');
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicle_details')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_reminder_logs');
    }
};
