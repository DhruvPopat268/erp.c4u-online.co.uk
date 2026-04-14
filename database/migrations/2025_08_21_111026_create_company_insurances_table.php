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
        Schema::create('company_insurances', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('company_id');
             $table->string('insurance_type');
             $table->date('insurance_date')->nullable();
            $table->timestamps();

                $table->foreign('company_id')->references('id')->on('company_details')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_insurances');
    }
};
