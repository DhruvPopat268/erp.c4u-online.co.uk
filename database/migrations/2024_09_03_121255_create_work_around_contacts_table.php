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
        Schema::create('work_around_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('name')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('address')->nullable();
            $table->string('designation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_around_contacts');
    }
};
