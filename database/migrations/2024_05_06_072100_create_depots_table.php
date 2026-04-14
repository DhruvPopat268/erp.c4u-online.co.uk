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
        Schema::create('depots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('companyName');
            $table->string('licence_number');
            $table->string('traffic_area');
            $table->string('continuation_date');
            $table->string('transport_manager_name');
            $table->string('operating_centre');
            $table->integer('vehicles');
            $table->integer('trailers');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depots');
    }
};
