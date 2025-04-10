<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMvtsToPerformanceMetricRulesTable extends Migration
{
    public function up()
    {
        Schema::table('performance_metric_rules', function (Blueprint $table) {
            $table->decimal('mvts_divisor', 8, 3)->default(0.135)->comment('Divisor used for calculating MVtS (Maintenance Variance to Standard)');
        });
    }

    public function down()
    {
        Schema::table('performance_metric_rules', function (Blueprint $table) {
            $table->dropColumn('mvts_divisor');
        });
    }
}