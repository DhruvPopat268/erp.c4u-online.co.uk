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
        Schema::create('company_details', function (Blueprint $table) {
              $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->string('address');
            $table->string('contact');
            $table->string('director_name')->nullable();
            $table->string('director_dob')->nullable();
            $table->string('device')->nullable();
            $table->string('operator_name')->nullable();
            $table->string('operator_phone')->nullable();
            $table->string('status')->nullable();
            $table->string('compliance')->nullable();
            $table->string('operator_email')->nullable();
           
            $table->integer('created_username');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_details');
    }
};
