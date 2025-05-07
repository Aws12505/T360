<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateSafetyThresholdsTable
 *
 * Creates the 'safety_thresholds' table for storing threshold values for safety metrics.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('safety_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade')->comment('Tenant associated with the threshold');
            $table->string('metric_name')->comment('Name of the safety metric (column in safety_data)');
            $table->decimal('good_threshold', 10, 4)->nullable()->comment('Threshold value for good performance');
            $table->decimal('bad_threshold', 10, 4)->nullable()->comment('Threshold value for bad performance');
            $table->boolean('good_enabled')->default(false)->comment('Whether the good threshold is enabled');
            $table->boolean('bad_enabled')->default(false)->comment('Whether the bad threshold is enabled');
            $table->timestamps();

            // Add a unique constraint to ensure each metric has only one threshold per tenant
            $table->unique(['tenant_id', 'metric_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('safety_thresholds');
    }
};