<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: AddVmcrPColumnsToPerformanceMetricRulesTable
 *
 * Adds VMCR-P threshold columns to the performance_metric_rules table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('performance_metric_rules', function (Blueprint $table) {
            // VMCR-P thresholds
            $table->integer('vmcr_p_fantastic_plus')->nullable()->comment('Threshold for fantastic plus VMCR-P');
            $table->enum('vmcr_p_fantastic_plus_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic plus VMCR-P');
            $table->integer('vmcr_p_fantastic')->nullable()->comment('Threshold for fantastic VMCR-P');
            $table->enum('vmcr_p_fantastic_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fantastic VMCR-P');
            $table->integer('vmcr_p_good')->nullable()->comment('Threshold for good VMCR-P');
            $table->enum('vmcr_p_good_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for good VMCR-P');
            $table->integer('vmcr_p_fair')->nullable()->comment('Threshold for fair VMCR-P');
            $table->enum('vmcr_p_fair_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for fair VMCR-P');
            $table->integer('vmcr_p_poor')->nullable()->comment('Threshold for poor VMCR-P');
            $table->enum('vmcr_p_poor_operator', [
                'less',
                'equal',
                'less_or_equal',
                'more_or_equal',
                'more',
            ])->default('equal')->nullable()->comment('Operator for poor VMCR-P');
        });
    }

    public function down(): void
    {
        Schema::table('performance_metric_rules', function (Blueprint $table) {
            $table->dropColumn([
                'vmcr_p_fantastic_plus',
                'vmcr_p_fantastic_plus_operator',
                'vmcr_p_fantastic',
                'vmcr_p_fantastic_operator',
                'vmcr_p_good',
                'vmcr_p_good_operator',
                'vmcr_p_fair',
                'vmcr_p_fair_operator',
                'vmcr_p_poor',
                'vmcr_p_poor_operator',
            ]);
        });
    }
};