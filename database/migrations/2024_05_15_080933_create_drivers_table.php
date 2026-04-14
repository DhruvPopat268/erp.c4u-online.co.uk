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
        Schema::create('drivers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('companyName');
            $table->enum('driver_status', ['Active', 'Inactive'])->default('Active');
            $table->string('ni_number')->nullable();
            $table->string('post_code')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('driver_dob')->nullable();
            $table->string('driver_address')->nullable();
            $table->string('driver_licence_no')->nullable();
            $table->string('driver_licence_status')->nullable();
            $table->string('driver_licence_expiry')->nullable();
            $table->string('cpc_status')->nullable();
            $table->string('cpc_validto')->nullable();
            $table->string('tacho_card_no')->nullable()->default('-');
            $table->string('tacho_card_status')->nullable()->default('-');
            $table->string('tacho_card_valid_from')->nullable()->default('-');
            $table->string('tacho_card_valid_to')->nullable()->default('-');
            $table->string('lc_check_status')->nullable()->default('-');
            $table->string('latest_lc_check')->nullable();
            $table->string('comment')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });
    }
    // public function up()
    // {
    //     Schema::create('drivers', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('driving_licence_number')->unique();
    //         $table->string('last_name');
    //         $table->string('gender');
    //         $table->string('first_names');
    //         $table->date('date_of_birth');
    //         $table->string('address_line1');
    //         $table->string('address_line5');
    //         $table->string('postcode');
    //         $table->string('licence_type');
    //         $table->string('licence_status');
    //         $table->timestamps();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
