<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreatePerformancesTable
 *
 * Creates the 'performances' table to store daily performance records.
 */
class CreatePerformancesTable extends Migration
{
    public function up()
    {
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade')->comment('Tenant associated with the performance record');
            $table->date('date')->comment('Date of performance record');
            $table->decimal('acceptance', 8, 2)->comment('Acceptance percentage');
            $table->decimal('on_time_to_origin', 8, 2)->comment('On time percentage to origin');
            $table->decimal('on_time_to_destination', 8, 2)->comment('On time percentage to destination');
            $table->decimal('on_time', 8, 2)->comment('Composite on time percentage');
            $table->decimal('maintenance_variance_to_spend', 8, 2)->comment('Maintenance variance metric');
            $table->integer('open_boc')->comment('Open BOC count');
            $table->boolean('meets_safety_bonus_criteria')->default(false)->comment('Indicates if safety bonus criteria is met');
            $table->integer('vcr_preventable')->comment('VCR preventable count');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('performances');
    }
}
