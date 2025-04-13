<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: AddVmcrPColumnsToPerformancesTable
 *
 * Adds VMCR-P metric column and its rating column to the performances table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->integer('vmcr_p')->comment('VMCR-P count');
            $table->string('vmcr_p_rating')->comment('Calculated rating for VMCR-P');
        });
    }

    public function down(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->dropColumn([
                'vmcr_p',
                'vmcr_p_rating',
            ]);
        });
    }
};