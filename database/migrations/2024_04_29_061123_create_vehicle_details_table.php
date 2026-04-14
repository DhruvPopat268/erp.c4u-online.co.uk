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
        Schema::create('vehicle_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('companyName');
            $table->string('registrationNumber');
            $table->string('taxStatus')->nullable();
            $table->string('taxDueDate')->nullable();
            $table->string('motStatus')->nullable();
            $table->string('make')->nullable();
            $table->string('yearOfManufacture')->nullable();
            $table->string('engineCapacity')->nullable();
            $table->string('co2Emissions')->nullable();
            $table->string('fuelType')->nullable();
            $table->string('markedForExport')->nullable();
            $table->string('colour')->nullable();
            $table->string('typeApproval')->nullable();
            $table->string('revenueWeight')->nullable();
            $table->string('euroStatus')->nullable();
            $table->string('dateOfLastV5CIssued')->nullable();
            $table->string('motExpiryDate')->nullable();
            $table->string('wheelplan')->nullable();
            $table->string('monthOfFirstRegistration')->nullable();
            $table->string('tacho_calibration')->nullable();
            $table->string('dvs_pss_permit_expiry')->nullable();
            $table->string('insurance_type')->nullable();
            $table->string('insurance')->nullable();
            $table->string('PMI_intervals')->nullable();
            $table->string('PMI_due')->nullable();
            $table->string('date_of_inspection')->nullable();
            $table->string('odometer_reading')->nullable();
            $table->string('brake_test_due')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_details');
    }
};
