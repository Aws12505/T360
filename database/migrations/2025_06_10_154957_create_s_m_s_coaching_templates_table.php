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
        Schema::create('s_m_s_coaching_templates', function (Blueprint $table) {
            $table->id();
            $table->string('coaching_message', 150); // string with 150 char limit

            // Enum fields: 'good', 'bad', 'minor_improvement'
            $table->enum('acceptance', ['good', 'bad', 'minor_improvement']);
            $table->enum('ontime', ['good', 'bad', 'minor_improvement']);
            $table->enum('greenzone', ['good', 'bad', 'minor_improvement']);
            $table->enum('severe_alerts', ['good', 'bad', 'minor_improvement']);

            $table->foreignId('tenant_id')->unique()->constrained()->onDelete('cascade')->comment('Tenant associated with the miles driven record');

            $table->timestamps();
            $table->unique(
                ['tenant_id', 'acceptance', 'ontime', 'greenzone', 'severe_alerts'],
                'unique_tenant_behavior_combination'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_m_s_coaching_templates');
    }
};
