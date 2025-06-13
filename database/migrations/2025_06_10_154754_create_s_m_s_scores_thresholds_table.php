<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('s_m_s_scores_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->onDelete('cascade')->comment('Tenant associated with the miles driven record');
            // On-time (percentage)
            $table->decimal('on_time_good', 5, 2);
            $table->decimal('on_time_bad', 5, 2);
            $table->decimal('on_time_minor_improvement', 5, 2);
            // Acceptance (percentage)
            $table->decimal('acceptance_good', 5, 2);
            $table->decimal('acceptance_bad', 5, 2);
            $table->decimal('acceptance_minor_improvement', 5, 2);
            // Green Zone Score (out of 1050)
            $table->decimal('greenzone_score_good', 6, 2);
            $table->decimal('greenzone_score_bad', 6, 2);
            $table->decimal('greenzone_score_minor_improvement', 6, 2);
            // Severe Alerts (integer)
            $table->integer('severe_alerts_good');
            $table->integer('severe_alerts_bad');
            $table->integer('severe_alerts_minor_improvement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_m_s_scores_thresholds');
    }
};
