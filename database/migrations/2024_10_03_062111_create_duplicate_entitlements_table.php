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
        Schema::create('duplicate_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('duplicate_drivers')->onDelete('cascade');
            $table->string('category_code');
            $table->string('category_legal_literal');
            $table->string('category_type');
            $table->string('from_date')->nullable();
            $table->string('expiry_date')->nullable();
            $table->json('restrictions')->nullable();
            $table->string('restriction_code')->nullable();
            $table->string('restriction_literal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duplicate_entitlements');
    }
};
