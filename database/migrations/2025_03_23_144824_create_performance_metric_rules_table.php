<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerformanceMetricRulesTable extends Migration
{
    public function up()
    {
        Schema::create('performance_metric_rules', function (Blueprint $table) {
            $table->id();

            // Acceptance
            $table->decimal('acceptance_fantastic_plus', 5, 2)->nullable();
            $table->string('acceptance_fantastic_plus_operator')->nullable();
            $table->decimal('acceptance_fantastic', 5, 2)->nullable();
            $table->string('acceptance_fantastic_operator')->nullable();
            $table->decimal('acceptance_good', 5, 2)->nullable();
            $table->string('acceptance_good_operator')->nullable();
            $table->decimal('acceptance_fair', 5, 2)->nullable();
            $table->string('acceptance_fair_operator')->nullable();
            $table->decimal('acceptance_poor', 5, 2)->nullable();
            $table->string('acceptance_poor_operator')->nullable();

            // On-Time
            $table->decimal('on_time_fantastic_plus', 5, 2)->nullable();
            $table->string('on_time_fantastic_plus_operator')->nullable();
            $table->decimal('on_time_fantastic', 5, 2)->nullable();
            $table->string('on_time_fantastic_operator')->nullable();
            $table->decimal('on_time_good', 5, 2)->nullable();
            $table->string('on_time_good_operator')->nullable();
            $table->decimal('on_time_fair', 5, 2)->nullable();
            $table->string('on_time_fair_operator')->nullable();
            $table->decimal('on_time_poor', 5, 2)->nullable();
            $table->string('on_time_poor_operator')->nullable();

            // Maintenance Variance
            $table->decimal('maintenance_variance_fantastic_plus', 5, 2)->nullable();
            $table->string('maintenance_variance_fantastic_plus_operator')->nullable();
            $table->decimal('maintenance_variance_fantastic', 5, 2)->nullable();
            $table->string('maintenance_variance_fantastic_operator')->nullable();
            $table->decimal('maintenance_variance_good', 5, 2)->nullable();
            $table->string('maintenance_variance_good_operator')->nullable();
            $table->decimal('maintenance_variance_fair', 5, 2)->nullable();
            $table->string('maintenance_variance_fair_operator')->nullable();
            $table->decimal('maintenance_variance_poor', 5, 2)->nullable();
            $table->string('maintenance_variance_poor_operator')->nullable();

            // Open BOC
            $table->integer('open_boc_fantastic_plus')->nullable();
            $table->string('open_boc_fantastic_plus_operator')->nullable();
            $table->integer('open_boc_fantastic')->nullable();
            $table->string('open_boc_fantastic_operator')->nullable();
            $table->integer('open_boc_good')->nullable();
            $table->string('open_boc_good_operator')->nullable();
            $table->integer('open_boc_fair')->nullable();
            $table->string('open_boc_fair_operator')->nullable();
            $table->integer('open_boc_poor')->nullable();
            $table->string('open_boc_poor_operator')->nullable();

            // Safety Bonus: yes/no toggle only
            $table->json('safety_bonus_eligible_levels')->nullable();

            // VCR Preventable
            $table->integer('vcr_preventable_fantastic_plus')->nullable();
            $table->string('vcr_preventable_fantastic_plus_operator')->nullable();
            $table->integer('vcr_preventable_fantastic')->nullable();
            $table->string('vcr_preventable_fantastic_operator')->nullable();
            $table->integer('vcr_preventable_good')->nullable();
            $table->string('vcr_preventable_good_operator')->nullable();
            $table->integer('vcr_preventable_fair')->nullable();
            $table->string('vcr_preventable_fair_operator')->nullable();
            $table->integer('vcr_preventable_poor')->nullable();
            $table->string('vcr_preventable_poor_operator')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('performance_metric_rules');
    }
}
