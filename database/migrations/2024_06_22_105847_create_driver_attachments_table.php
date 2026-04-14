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
        Schema::create('driver_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('driver_id');
            $table->string('license_front');
            $table->string('license_back');
            $table->string('cpc_card_front');
            $table->string('cpc_card_back');
            $table->string('tacho_card_front');
            $table->string('tacho_card_back');
            $table->string('mpqc_card_front');
            $table->string('mpqc_card_back');
            $table->string('levelD_card_front');
            $table->string('levelD_card_back');
            $table->string('one_card_front');
            $table->string('one_card_back');
            $table->json('additional_cards')->change();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_attachments');
    }
};
