<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDisputeFieldsOnRepairOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('repair_orders', function (Blueprint $table) {

            // Drop old columns
            $table->dropColumn(['disputed', 'dispute_outcome']);

            // Add new columns
            $table->enum('dispute_review_status', [
                'None',
                'Pending',
                'Reviewed',
                'Overcharged'
            ])->default('None')->after('qs_invoice_date');

            $table->enum('dispute_review_determination', [
                'Granted',
                'Partially Granted',
                'Valid',
                'Valid Charge'
            ])->nullable()->after('dispute_review_status');

            $table->decimal('dispute_outcome', 15, 2)
                ->nullable()
                ->after('dispute_review_determination');

            $table->decimal('original_amount', 15, 2)
                ->nullable()
                ->after('dispute_outcome');
        });
    }

    public function down()
    {
        Schema::table('repair_orders', function (Blueprint $table) {

            // Drop new columns
            $table->dropColumn([
                'dispute_review_status',
                'dispute_review_determination',
                'dispute_outcome',
                'original_amount'
            ]);

            // Restore old columns
            $table->boolean('disputed')->after('qs_invoice_date');
            $table->longText('dispute_outcome')->nullable()->after('disputed');
        });
    }
}
