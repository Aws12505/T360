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
        Schema::create('sms_coaching_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key', 64);
            $table->decimal('good', 8, 2)->nullable();
            $table->decimal('minor_improvement', 8, 2)->nullable();
            $table->decimal('bad', 8, 2)->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'metric_key'], 'sms_coaching_thresholds_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_coaching_thresholds');
    }
};
