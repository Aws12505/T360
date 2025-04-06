<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: AddSafetyMetricsToPerformanceMetricRules
 *
 * Adds safety metrics with gold, silver, and not_eligible tiers to the performance_metric_rules table.
 */
class AddSafetyMetricsToPerformanceMetricRules extends Migration
{
    public function up()
    {
        Schema::table('performance_metric_rules', function (Blueprint $table) {
            // Driver Distraction thresholds
            $table->decimal('driver_distraction_gold', 5, 2)->nullable()->comment('Threshold for gold driver distraction');
            $table->string('driver_distraction_gold_operator')->nullable()->comment('Operator for gold driver distraction');
            $table->decimal('driver_distraction_silver', 5, 2)->nullable()->comment('Threshold for silver driver distraction');
            $table->string('driver_distraction_silver_operator')->nullable()->comment('Operator for silver driver distraction');
            $table->decimal('driver_distraction_not_eligible', 5, 2)->nullable()->comment('Threshold for not eligible driver distraction');
            $table->string('driver_distraction_not_eligible_operator')->nullable()->comment('Operator for not eligible driver distraction');

            // Speeding Violation thresholds
            $table->decimal('speeding_violation_gold', 5, 2)->nullable()->comment('Threshold for gold speeding violation');
            $table->string('speeding_violation_gold_operator')->nullable()->comment('Operator for gold speeding violation');
            $table->decimal('speeding_violation_silver', 5, 2)->nullable()->comment('Threshold for silver speeding violation');
            $table->string('speeding_violation_silver_operator')->nullable()->comment('Operator for silver speeding violation');
            $table->decimal('speeding_violation_not_eligible', 5, 2)->nullable()->comment('Threshold for not eligible speeding violation');
            $table->string('speeding_violation_not_eligible_operator')->nullable()->comment('Operator for not eligible speeding violation');

            // Sign Violation thresholds
            $table->decimal('sign_violation_gold', 5, 2)->nullable()->comment('Threshold for gold sign violation');
            $table->string('sign_violation_gold_operator')->nullable()->comment('Operator for gold sign violation');
            $table->decimal('sign_violation_silver', 5, 2)->nullable()->comment('Threshold for silver sign violation');
            $table->string('sign_violation_silver_operator')->nullable()->comment('Operator for silver sign violation');
            $table->decimal('sign_violation_not_eligible', 5, 2)->nullable()->comment('Threshold for not eligible sign violation');
            $table->string('sign_violation_not_eligible_operator')->nullable()->comment('Operator for not eligible sign violation');

            // Traffic Light Violation thresholds
            $table->decimal('traffic_light_violation_gold', 5, 2)->nullable()->comment('Threshold for gold traffic light violation');
            $table->string('traffic_light_violation_gold_operator')->nullable()->comment('Operator for gold traffic light violation');
            $table->decimal('traffic_light_violation_silver', 5, 2)->nullable()->comment('Threshold for silver traffic light violation');
            $table->string('traffic_light_violation_silver_operator')->nullable()->comment('Operator for silver traffic light violation');
            $table->decimal('traffic_light_violation_not_eligible', 5, 2)->nullable()->comment('Threshold for not eligible traffic light violation');
            $table->string('traffic_light_violation_not_eligible_operator')->nullable()->comment('Operator for not eligible traffic light violation');

            // Following Distance thresholds
            $table->decimal('following_distance_gold', 5, 2)->nullable()->comment('Threshold for gold following distance');
            $table->string('following_distance_gold_operator')->nullable()->comment('Operator for gold following distance');
            $table->decimal('following_distance_silver', 5, 2)->nullable()->comment('Threshold for silver following distance');
            $table->string('following_distance_silver_operator')->nullable()->comment('Operator for silver following distance');
            $table->decimal('following_distance_not_eligible', 5, 2)->nullable()->comment('Threshold for not eligible following distance');
            $table->string('following_distance_not_eligible_operator')->nullable()->comment('Operator for not eligible following distance');
        });
    }

    public function down()
    {
        Schema::table('performance_metric_rules', function (Blueprint $table) {
            // Driver Distraction
            $table->dropColumn([
                'driver_distraction_gold',
                'driver_distraction_gold_operator',
                'driver_distraction_silver',
                'driver_distraction_silver_operator',
                'driver_distraction_not_eligible',
                'driver_distraction_not_eligible_operator',
            ]);

            // Speeding Violation
            $table->dropColumn([
                'speeding_violation_gold',
                'speeding_violation_gold_operator',
                'speeding_violation_silver',
                'speeding_violation_silver_operator',
                'speeding_violation_not_eligible',
                'speeding_violation_not_eligible_operator',
            ]);

            // Sign Violation
            $table->dropColumn([
                'sign_violation_gold',
                'sign_violation_gold_operator',
                'sign_violation_silver',
                'sign_violation_silver_operator',
                'sign_violation_not_eligible',
                'sign_violation_not_eligible_operator',
            ]);

            // Traffic Light Violation
            $table->dropColumn([
                'traffic_light_violation_gold',
                'traffic_light_violation_gold_operator',
                'traffic_light_violation_silver',
                'traffic_light_violation_silver_operator',
                'traffic_light_violation_not_eligible',
                'traffic_light_violation_not_eligible_operator',
            ]);

            // Following Distance
            $table->dropColumn([
                'following_distance_gold',
                'following_distance_gold_operator',
                'following_distance_silver',
                'following_distance_silver_operator',
                'following_distance_not_eligible',
                'following_distance_not_eligible_operator',
            ]);
        });
    }
}