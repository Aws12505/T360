<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateRejectionReasonCodesTable
 *
 * Creates the 'rejection_reason_codes' table to store unique codes for rejection reasons.
 */
class CreateRejectionReasonCodesTable extends Migration
{
    public function up()
    {
        Schema::create('rejection_reason_codes', function (Blueprint $table) {
            $table->id();
            $table->string('reason_code',75)->unique()->comment('Unique rejection reason code');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rejection_reason_codes');
    }
}
