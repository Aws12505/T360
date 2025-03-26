<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSafetyDataTable extends Migration
{
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
            $table->decimal('total_events_avg_fd_impact', 10, 2)->nullable();
            $table->decimal('average_following_distance_sec', 10, 2)->nullable();
            $table->decimal('average_following_distance_gz_impact', 10, 2)->nullable();
            $table->decimal('total_events', 10, 2)->nullable();
            $table->decimal('high_g', 10, 2)->nullable();
            $table->decimal('low_impact', 10, 2)->nullable();
            $table->decimal('driver_initiated', 10, 2)->nullable();
            $table->decimal('potential_collision', 10, 2)->nullable();
            $table->decimal('sign_violations', 10, 2)->nullable();
            $table->decimal('sign_violations_gz_impact', 10, 2)->nullable();
            $table->decimal('traffic_light_violation', 10, 2)->nullable();
            $table->decimal('traffic_light_violation_gz_impact', 10, 2)->nullable();
            $table->decimal('u_turn', 10, 2)->nullable();
            $table->decimal('u_turn_gz_impact', 10, 2)->nullable();
            $table->decimal('hard_braking', 10, 2)->nullable();
            $table->decimal('hard_braking_gz_impact', 10, 2)->nullable();
            $table->decimal('hard_turn', 10, 2)->nullable();
            $table->decimal('hard_turn_gz_impact', 10, 2)->nullable();
            $table->decimal('hard_acceleration', 10, 2)->nullable();
            $table->decimal('hard_acceleration_gz_impact', 10, 2)->nullable();
            $table->decimal('driver_distraction', 10, 2)->nullable();
            $table->decimal('driver_distraction_gz_impact', 10, 2)->nullable();
            $table->decimal('following_distance', 10, 2)->nullable();
            $table->decimal('following_distance_gz_impact', 10, 2)->nullable();
            $table->decimal('speeding_violations', 10, 2)->nullable();
            $table->decimal('speeding_violations_gz_impact', 10, 2)->nullable();
            $table->decimal('seatbelt_compliance', 10, 2)->nullable();
            $table->decimal('camera_obstruction', 10, 2)->nullable();
            $table->decimal('driver_drowsiness', 10, 2)->nullable();
            $table->decimal('weaving', 10, 2)->nullable();
            $table->decimal('weaving_gz_impact', 10, 2)->nullable();
            $table->decimal('collision_warning', 10, 2)->nullable();
            $table->decimal('collision_warning_gz_impact', 10, 2)->nullable();
            $table->decimal('requested_video', 10, 2)->nullable();
            $table->decimal('backing', 10, 2)->nullable();
            $table->decimal('roadside_parking', 10, 2)->nullable();
            $table->decimal('driver_distracted_hard_brake', 10, 2)->nullable();
            $table->decimal('following_distance_hard_brake', 10, 2)->nullable();
            $table->decimal('driver_distracted_following_distance', 10, 2)->nullable();
            $table->decimal('driver_star', 10, 2)->nullable();
            $table->decimal('driver_star_gz_impact', 10, 2)->nullable();
            $table->string('vehicle_type')->nullable();
            $table->decimal('safety_normalisation_factor', 10, 2)->nullable();
            $table->date('date');
            $table->timestamps();
            $table->unique(['user_name', 'date', 'tenant_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('safety_data');
    }
}
