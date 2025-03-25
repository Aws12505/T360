<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSafetyDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('safety_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('driver_name')->nullable();
            $table->string('user_name'); 
            $table->string('group')->nullable();
            $table->string('group_hierarchy')->nullable();
            $table->decimal('minutes_analyzed', 10, 2)->nullable();
            $table->decimal('green_minutes_percent', 10, 2)->nullable();
            $table->decimal('overspeeding_percent', 10, 2)->nullable();
            $table->decimal('driver_score', 10, 2)->nullable();
            $table->decimal('total_events', 10, 2)->nullable();
            $table->decimal('average_following_distance', 10, 2)->nullable();
            $table->integer('sign_violations')->nullable();
            $table->integer('traffic_light_violation')->nullable();
            $table->integer('driver_distraction')->nullable();
            $table->integer('following_distance')->nullable();
            $table->integer('speeding_violations')->nullable();
            $table->integer('driver_drowsiness')->nullable();
            $table->integer('driver_star')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->decimal('safety_normalisation_factor', 10, 2)->nullable();
            $table->date('date'); 
            $table->timestamps();
        
            $table->unique(['user_name', 'date','tenant_id']); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('safety_data');
    }
}
