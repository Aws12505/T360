<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantMetricsRulesTable extends Migration
{
    public function up()
    {
        Schema::create('tenant_metrics_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->onDelete('cascade')->comment('Tenant associated with the miles driven record');
            $table->decimal('mvts_divisor', 8, 3)->default(0.135); // decimal(8,3)
            $table->timestamps();

            // Add foreign key constraint if tenant_id references another table
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_metrics_rules');
    }
}
