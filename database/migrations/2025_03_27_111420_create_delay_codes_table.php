<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateDelayCodesTable
 *
 * Creates the 'delay_codes' table to store unique delay codes.
 */
class CreateDelayCodesTable extends Migration
{
    public function up()
    {
        Schema::create('delay_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Unique delay code');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delay_codes');
    }
}
