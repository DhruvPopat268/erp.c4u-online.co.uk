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
        Schema::create('app_access_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->json('manager_access')->nullable();
            $table->json('driver_access')->nullable();
            $table->timestamps();

            // Foreign key relation (Assuming a 'companies' table exists)
            $table->foreign('company_id')->references('id')->on('company_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_access_levels');
    }
};
