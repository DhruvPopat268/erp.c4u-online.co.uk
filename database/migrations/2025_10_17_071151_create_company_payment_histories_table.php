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
        Schema::create('company_payment_histories', function (Blueprint $table) {
          $table->id();
        $table->unsignedBigInteger('company_id');
        $table->string('old_payment_type')->nullable();
        $table->string('new_payment_type');
        $table->integer('old_coins')->nullable();
        $table->integer('new_coins')->nullable();
        $table->unsignedBigInteger('changed_by'); // user ID who made change
        $table->timestamps();

        $table->foreign('company_id')->references('id')->on('company_details')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_payment_histories');
    }
};
