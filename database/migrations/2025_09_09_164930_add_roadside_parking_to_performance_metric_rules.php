<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: AddRoadsideParkingToPerformanceMetricRules
 *
 * Adds roadside parking metrics with gold, silver, and not_eligible tiers
 * to the performance_metric_rules table.
 */
class AddRoadsideParkingToPerformanceMetricRules extends Migration
{
    public function up()
    {
        Schema::table('performance_metric_rules', function (Blueprint $table) {
            // Roadside Parking thresholds
            $table->decimal('roadside_parking_gold', 5, 2)
                ->nullable()
                ->comment('Threshold for gold roadside parking');
            $table->enum('roadside_parking_gold_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()
                ->comment('Operator for gold roadside parking');

            $table->decimal('roadside_parking_silver', 5, 2)
                ->nullable()
                ->comment('Threshold for silver roadside parking');
            $table->enum('roadside_parking_silver_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()
                ->comment('Operator for silver roadside parking');

            $table->decimal('roadside_parking_not_eligible', 5, 2)
                ->nullable()
                ->comment('Threshold for not eligible roadside parking');
            $table->enum('roadside_parking_not_eligible_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()
                ->comment('Operator for not eligible roadside parking');
        });
    }

    public function down()
    {
        Schema::table('performance_metric_rules', function (Blueprint $table) {
            $table->dropColumn([
                'roadside_parking_gold',
                'roadside_parking_gold_operator',
                'roadside_parking_silver',
                'roadside_parking_silver_operator',
                'roadside_parking_not_eligible',
                'roadside_parking_not_eligible_operator',
            ]);
        });
    }
}
