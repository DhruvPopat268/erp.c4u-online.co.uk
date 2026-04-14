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
        Schema::create('driver_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id'); // Foreign key to drivers table
            $table->unsignedBigInteger('company_id');
            $table->string('reminder_type'); // The type of reminder (driver_licence_expiry, cpc_validto, tacho_card_valid_to)
            $table->date('reminder_date'); // The date the reminder is for
            $table->enum('status', ['Pending', 'Sent', 'Failed'])->default('Pending');
            $table->timestamps(); // Timestamps for created_at and updated_at

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_reminder_logs');
    }
};
