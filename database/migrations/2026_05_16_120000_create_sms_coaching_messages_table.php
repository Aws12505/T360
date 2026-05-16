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
        Schema::create('sms_coaching_messages', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key', 64);
            $table->string('status', 32)->nullable();
            $table->string('message', 400);
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'metric_key', 'status'], 'sms_coaching_messages_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_coaching_messages');
    }
};
