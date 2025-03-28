<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: UpdatePerformancesTableAddRatings
 *
 * Adds rating columns to the performances table for storing calculated performance ratings.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->string('acceptance_rating')->comment('Calculated rating for acceptance');
            $table->string('on_time_rating')->comment('Calculated rating for on time performance');
            $table->string('maintenance_variance_to_spend_rating')->comment('Calculated rating for maintenance variance');
            $table->string('open_boc_rating')->comment('Calculated rating for open BOC');
            $table->string('meets_safety_bonus_criteria_rating')->comment('Calculated rating for safety bonus criteria');
            $table->string('vcr_preventable_rating')->comment('Calculated rating for VCR preventable');
        });
    }

    public function down(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->dropColumn([
                'acceptance_rating',
                'on_time_rating',
                'maintenance_variance_to_spend_rating',
                'open_boc_rating',
                'meets_safety_bonus_criteria_rating',
                'vcr_preventable_rating',
            ]);
        });
    }
};
