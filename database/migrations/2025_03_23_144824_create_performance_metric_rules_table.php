<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreatePerformanceMetricRulesTable
 *
 * Creates the 'performance_metric_rules' table to store thresholds and operators
 * for calculating performance ratings.
 */
class CreatePerformanceMetricRulesTable extends Migration
{
    public function up()
    {
        Schema::create('performance_metric_rules', function (Blueprint $table) {
            $table->id();

            // Acceptance thresholds
            $table->decimal('acceptance_fantastic_plus', 5, 2)->nullable()->comment('Threshold for fantastic plus acceptance');
            $table->enum('acceptance_fantastic_plus_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Comparison operator for fantastic plus acceptance');
            $table->decimal('acceptance_fantastic', 5, 2)->nullable()->comment('Threshold for fantastic acceptance');
            $table->enum('acceptance_fantastic_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Comparison operator for fantastic acceptance');
            $table->decimal('acceptance_good', 5, 2)->nullable()->comment('Threshold for good acceptance');
            $table->enum('acceptance_good_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Comparison operator for good acceptance');
            $table->decimal('acceptance_fair', 5, 2)->nullable()->comment('Threshold for fair acceptance');
            $table->enum('acceptance_fair_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Comparison operator for fair acceptance');
            $table->decimal('acceptance_poor', 5, 2)->nullable()->comment('Threshold for poor acceptance');
            $table->enum('acceptance_poor_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Comparison operator for poor acceptance');

            // On-Time thresholds
            $table->decimal('on_time_fantastic_plus', 5, 2)->nullable()->comment('Threshold for fantastic plus on-time performance');
            $table->enum('on_time_fantastic_plus_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic plus on-time performance');
            $table->decimal('on_time_fantastic', 5, 2)->nullable()->comment('Threshold for fantastic on-time performance');
            $table->enum('on_time_fantastic_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic on-time performance');
            $table->decimal('on_time_good', 5, 2)->nullable()->comment('Threshold for good on-time performance');
            $table->enum('on_time_good_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for good on-time performance');
            $table->decimal('on_time_fair', 5, 2)->nullable()->comment('Threshold for fair on-time performance');
            $table->enum('on_time_fair_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fair on-time performance');
            $table->decimal('on_time_poor', 5, 2)->nullable()->comment('Threshold for poor on-time performance');
            $table->enum('on_time_poor_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for poor on-time performance');

            // Maintenance Variance thresholds
            $table->decimal('maintenance_variance_fantastic_plus', 5, 2)->nullable()->comment('Threshold for fantastic plus maintenance variance');
            $table->enum('maintenance_variance_fantastic_plus_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic plus maintenance variance');
            $table->decimal('maintenance_variance_fantastic', 5, 2)->nullable()->comment('Threshold for fantastic maintenance variance');
            $table->enum('maintenance_variance_fantastic_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic maintenance variance');
            $table->decimal('maintenance_variance_good', 5, 2)->nullable()->comment('Threshold for good maintenance variance');
            $table->enum('maintenance_variance_good_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for good maintenance variance');
            $table->decimal('maintenance_variance_fair', 5, 2)->nullable()->comment('Threshold for fair maintenance variance');
            $table->enum('maintenance_variance_fair_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fair maintenance variance');
            $table->decimal('maintenance_variance_poor', 5, 2)->nullable()->comment('Threshold for poor maintenance variance');
            $table->enum('maintenance_variance_poor_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for poor maintenance variance');

            // Open BOC thresholds
            $table->integer('open_boc_fantastic_plus')->nullable()->comment('Threshold for fantastic plus open BOC');
            $table->enum('open_boc_fantastic_plus_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic plus open BOC');
            $table->integer('open_boc_fantastic')->nullable()->comment('Threshold for fantastic open BOC');
            $table->enum('open_boc_fantastic_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic open BOC');
            $table->integer('open_boc_good')->nullable()->comment('Threshold for good open BOC');
            $table->enum('open_boc_good_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for good open BOC');
            $table->integer('open_boc_fair')->nullable()->comment('Threshold for fair open BOC');
            $table->enum('open_boc_fair_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fair open BOC');
            $table->integer('open_boc_poor')->nullable()->comment('Threshold for poor open BOC');
            $table->enum('open_boc_poor_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for poor open BOC');

            // Safety Bonus eligible levels
            $table->json('safety_bonus_eligible_levels')->nullable()->comment('List of eligible levels for safety bonus');

            // VCR Preventable thresholds
            $table->integer('vcr_preventable_fantastic_plus')->nullable()->comment('Threshold for fantastic plus VCR preventable');
            $table->enum('vcr_preventable_fantastic_plus_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic plus VCR preventable');
            $table->integer('vcr_preventable_fantastic')->nullable()->comment('Threshold for fantastic VCR preventable');
            $table->enum('vcr_preventable_fantastic_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic VCR preventable');
            $table->integer('vcr_preventable_good')->nullable()->comment('Threshold for good VCR preventable');
            $table->enum('vcr_preventable_good_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for good VCR preventable');
            $table->integer('vcr_preventable_fair')->nullable()->comment('Threshold for fair VCR preventable');
            $table->enum('vcr_preventable_fair_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fair VCR preventable');
            $table->integer('vcr_preventable_poor')->nullable()->comment('Threshold for poor VCR preventable');
            $table->enum('vcr_preventable_poor_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for poor VCR preventable');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('performance_metric_rules');
    }
}
