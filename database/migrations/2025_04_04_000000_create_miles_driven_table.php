<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateMilesDrivenTable
 *
 * Creates the 'miles_driven' table for storing weekly miles driven records.
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
        Schema::create('miles_driven', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade')->comment('Tenant associated with the miles driven record');
            $table->date('week_start_date')->comment('Start date of the week');
            $table->date('week_end_date')->comment('End date of the week');
            $table->decimal('miles', 10, 4)->comment('Miles driven during the week');
            $table->text('notes')->nullable()->comment('Additional notes about the miles driven');
            $table->timestamps();
            
            // Add a unique constraint to prevent duplicate entries for the same week and tenant
            $table->unique(['tenant_id', 'week_start_date', 'week_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('miles_driven');
    }
};