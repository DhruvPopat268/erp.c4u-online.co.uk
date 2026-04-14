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
        Schema::create('vehicles_annual_tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('companyName');
            $table->string('vehicle_id');
            $table->string('test_date')->nullable();
            $table->string('test_type')->nullable();
            $table->string('test_result')->nullable();
            $table->string('test_certificate_number')->nullable();
            $table->string('expiry_date')->nullable();
            $table->string('number_of_defects_test')->nullable();
            $table->string('number_of_advisory_defects_test')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_annual_tests');
    }
};
