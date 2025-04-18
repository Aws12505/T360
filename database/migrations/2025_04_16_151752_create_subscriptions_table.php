<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // Link to tenants table
            $table->foreignId('tenant_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Unique Zoho subscription identifier
            $table->string('subscription_id')->unique();

            // Basic plan info
            $table->string('name',75)->nullable();
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency_code', 3)->nullable();

            // Billing dates (date only)
            $table->date('next_billing_at')->nullable();
            $table->date('last_billing_at');

            // Card details
            $table->year('expiry_year')->nullable();
            $table->tinyInteger('expiry_month')->nullable();
            $table->string('last_four_digits', 4)->nullable();
            $table->string('card_type',75)->nullable();
            $table->string('payment_gateway',75)->nullable();

            // Billing Address stored as JSON
            $table->json('billing_address')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
