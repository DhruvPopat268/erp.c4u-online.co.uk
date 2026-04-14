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
        Schema::create('driver_consent_forms', function (Blueprint $table) {
            $table->id();
            $table->string('companyName');
            $table->string('company_address');
            $table->string('postcode');
            $table->string('account_number');
            $table->string('reference_number');
            $table->string('making_an_enquiry');
            $table->string('making_an_enquiry_details');
            $table->string('reason_for_processing_information');
            $table->string('surname');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('date_of_birth');
            $table->string('current_address_line1');
            $table->string('current_address_line2');
            $table->string('current_address_line3');
            $table->string('current_address_posttown');
            $table->string('current_address_postcode');
            $table->string('licence_address_line1');
            $table->string('licence_address_line2');
            $table->string('licence_address_line3');
            $table->string('licence_address_posttown');
            $table->string('driver_licence_no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_consent_forms');
    }
};
