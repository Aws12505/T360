<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->string('acceptance_rating');
            $table->string('on_time_rating');
            $table->string('maintenance_variance_to_spend_rating');
            $table->string('open_boc_rating');
            $table->string('meets_safety_bonus_criteria_rating');
            $table->string('vcr_preventable_rating');
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
