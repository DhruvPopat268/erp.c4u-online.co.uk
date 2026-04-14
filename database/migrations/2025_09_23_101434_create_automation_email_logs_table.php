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
        Schema::create('automation_email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id')->nullable();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->unsignedBigInteger('user_id')->nullable(); // for manager
    $table->string('email');
    $table->string('subject');
    $table->longText('body');
    $table->enum('status', ['Pending', 'Sent'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_email_logs');
    }
};
