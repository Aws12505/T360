<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateSafetyDataTable
 *
 * Creates the 'safety_data' table to store various safety metrics per driver and date.
 */
class CreateSafetyDataTable extends Migration
{
    public function up()
    {
        Schema::create('safety_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete()->comment('Tenant associated with the safety data');
            $table->string('driver_name')->nullable()->comment('Driver name');
            $table->string('user_name')->comment('User name');
            $table->string('group')->nullable()->comment('Group name');
            $table->string('group_hierarchy')->nullable()->comment('Group hierarchy details');
            $table->decimal('minutes_analyzed', 10, 2)->nullable()->comment('Minutes analyzed');
            $table->decimal('green_minutes_percent', 10, 2)->nullable()->comment('Percentage of green minutes');
            $table->decimal('overspeeding_percent', 10, 2)->nullable()->comment('Percentage of overspeeding');
            $table->decimal('driver_score', 10, 2)->nullable()->comment('Driver score');
            $table->decimal('total_events_avg_fd_impact', 10, 2)->nullable()->comment('Average FD impact per event');
            $table->decimal('average_following_distance_sec', 10, 2)->nullable()->comment('Average following distance in seconds');
            $table->decimal('average_following_distance_gz_impact', 10, 2)->nullable()->comment('Impact of following distance');
            $table->decimal('total_events', 10, 2)->nullable()->comment('Total events recorded');
            $table->decimal('high_g', 10, 2)->nullable()->comment('High G-force events');
            $table->decimal('low_impact', 10, 2)->nullable()->comment('Low impact events');
            $table->decimal('driver_initiated', 10, 2)->nullable()->comment('Driver initiated events');
            $table->decimal('potential_collision', 10, 2)->nullable()->comment('Potential collision events');
            $table->decimal('sign_violations', 10, 2)->nullable()->comment('Count of sign violations');
            $table->decimal('sign_violations_gz_impact', 10, 2)->nullable()->comment('Impact of sign violations');
            $table->decimal('traffic_light_violation', 10, 2)->nullable()->comment('Traffic light violations');
            $table->decimal('traffic_light_violation_gz_impact', 10, 2)->nullable()->comment('Impact of traffic light violations');
            $table->decimal('u_turn', 10, 2)->nullable()->comment('Count of U-turns');
            $table->decimal('u_turn_gz_impact', 10, 2)->nullable()->comment('Impact of U-turns');
            $table->decimal('hard_braking', 10, 2)->nullable()->comment('Hard braking events');
            $table->decimal('hard_braking_gz_impact', 10, 2)->nullable()->comment('Impact of hard braking');
            $table->decimal('hard_turn', 10, 2)->nullable()->comment('Hard turn events');
            $table->decimal('hard_turn_gz_impact', 10, 2)->nullable()->comment('Impact of hard turns');
            $table->decimal('hard_acceleration', 10, 2)->nullable()->comment('Hard acceleration events');
            $table->decimal('hard_acceleration_gz_impact', 10, 2)->nullable()->comment('Impact of hard acceleration');
            $table->decimal('driver_distraction', 10, 2)->nullable()->comment('Driver distraction events');
            $table->decimal('driver_distraction_gz_impact', 10, 2)->nullable()->comment('Impact of driver distraction');
            $table->decimal('following_distance', 10, 2)->nullable()->comment('Following distance metric');
            $table->decimal('following_distance_gz_impact', 10, 2)->nullable()->comment('Impact of following distance');
            $table->decimal('speeding_violations', 10, 2)->nullable()->comment('Count of speeding violations');
            $table->decimal('speeding_violations_gz_impact', 10, 2)->nullable()->comment('Impact of speeding violations');
            $table->decimal('seatbelt_compliance', 10, 2)->nullable()->comment('Seatbelt compliance percentage');
            $table->decimal('camera_obstruction', 10, 2)->nullable()->comment('Camera obstruction events');
            $table->decimal('driver_drowsiness', 10, 2)->nullable()->comment('Driver drowsiness events');
            $table->decimal('weaving', 10, 2)->nullable()->comment('Weaving events');
            $table->decimal('weaving_gz_impact', 10, 2)->nullable()->comment('Impact of weaving events');
            $table->decimal('collision_warning', 10, 2)->nullable()->comment('Collision warning events');
            $table->decimal('collision_warning_gz_impact', 10, 2)->nullable()->comment('Impact of collision warnings');
            $table->decimal('requested_video', 10, 2)->nullable()->comment('Count of requested videos');
            $table->decimal('backing', 10, 2)->nullable()->comment('Backing events count');
            $table->decimal('roadside_parking', 10, 2)->nullable()->comment('Roadside parking events count');
            $table->decimal('driver_distracted_hard_brake', 10, 2)->nullable()->comment('Distracted hard brake events');
            $table->decimal('following_distance_hard_brake', 10, 2)->nullable()->comment('Following distance during hard brake');
            $table->decimal('driver_distracted_following_distance', 10, 2)->nullable()->comment('Distracted following distance events');
            $table->decimal('driver_star', 10, 2)->nullable()->comment('Driver star rating');
            $table->decimal('driver_star_gz_impact', 10, 2)->nullable()->comment('Impact on driver star rating');
            $table->string('vehicle_type')->nullable()->comment('Type of vehicle');
            $table->decimal('safety_normalisation_factor', 10, 2)->nullable()->comment('Safety normalisation factor');
            $table->date('date')->comment('Date of the safety data record');
            $table->timestamps();
            $table->unique(['user_name', 'date', 'tenant_id'], 'unique_safety_data');
        });
    }

    public function down()
    {
        Schema::dropIfExists('safety_data');
    }
}
