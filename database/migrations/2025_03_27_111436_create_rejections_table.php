<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateRejectionsTable
 *
 * Creates the 'rejections' table to record rejection events.
 */
class CreateRejectionsTable extends Migration
{
    public function up()
    {
        Schema::create('rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete()->comment('Tenant associated with the rejection');
            $table->date('date')->comment('Date of rejection event');
            $table->enum('rejection_type', ['block', 'load'])->comment('Type of rejection');
            $table->string('driver_name')->comment('Driver name');
            $table->enum('rejection_category', ['more_than_6', 'within_6', 'after_start'])->comment('Rejection time category');
            $table->integer('penalty')->comment('Computed penalty for the rejection');
            $table->foreignId('reason_code_id')->constrained('rejection_reason_codes')->cascadeOnDelete()->comment('Associated rejection reason code');
            $table->boolean('disputed')->comment('Indicates if the rejection is disputed');
            $table->boolean('driver_controllable')->nullable()->comment('Indicates if the rejection is driver controllable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rejections');
    }
}
