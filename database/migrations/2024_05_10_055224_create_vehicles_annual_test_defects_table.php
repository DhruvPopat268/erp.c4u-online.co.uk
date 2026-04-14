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
        Schema::create('vehicles_annual_test_defects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('companyName');
            $table->string('vehicle_id');
            $table->string('annual_test_id')->nullable();
            $table->string('failure_item_no')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('severity_code')->nullable();
            $table->string('severity_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_annual_test_defects');
    }
};
