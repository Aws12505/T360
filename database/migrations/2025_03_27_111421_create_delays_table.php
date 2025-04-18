<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateDelaysTable
 *
 * Creates the 'delays' table to record delay events.
 */
class CreateDelaysTable extends Migration
{
    public function up()
    {
        Schema::create('delays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade')->comment('Tenant associated with the delay');
            $table->date('date')->comment('Date of delay event');
            $table->enum('delay_type', ['origin', 'destination'])->comment('Indicates if the delay occurred at origin or destination');
            $table->string('driver_name',75)->comment('Driver name');
            $table->enum('delay_category', ['1_120', '121_600', '601_plus'])->comment('Delay category determining the penalty');
            $table->integer('penalty')->comment('Computed penalty value');
            $table->foreignId('delay_code_id')->constrained()->onDelete('cascade')->comment('References the delay code');
            $table->boolean('disputed')->comment('Indicates if the delay is disputed');
            $table->boolean('driver_controllable')->nullable()->comment('Indicates if the delay is controllable by the driver');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delays');
    }
}
