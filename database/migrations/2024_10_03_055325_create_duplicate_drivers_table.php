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
        Schema::create('duplicate_drivers', function (Blueprint $table) {
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
            $table->string('latest_lc_check')->nullable();
            $table->string('comment')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_names')->nullable();
            $table->string('gender')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('address_line3')->nullable();
            $table->string('address_line4')->nullable();
            $table->string('address_line5')->nullable();
            $table->string('licence_type')->nullable();
            $table->string('endorsement_penalty_points')->nullable();
            $table->string('endorsement_offence_code')->nullable();
            $table->string('endorsement_offence_legal_literal')->nullable();
            $table->string('endorsement_offence_date')->nullable();
            $table->string('endorsement_conviction_date')->nullable();
            $table->string('token_issue_number')->nullable();
            $table->string('token_valid_from_date')->nullable();
            $table->string('dqc_issue_date')->nullable();
            $table->string('insurance_reminder_sent_at')->nullable();

            $table->string('endorsements')->nullable();
            $table->string('current_licence_check_interval')->nullable();
            $table->string('content_valid_until')->nullable();
            $table->string('device_token')->nullable();
            $table->string('group_id')->nullable();
            $table->string('automation')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duplicate_drivers');
    }
};
