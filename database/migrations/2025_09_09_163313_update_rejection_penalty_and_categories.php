<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRejectionPenaltyAndCategories extends Migration
{
    public function up()
    {
        Schema::table('rejections', function (Blueprint $table) {
            // Change penalty from integer → decimal (e.g. 10,2 means max 99999999.99)
            $table->decimal('penalty', 10, 2)->comment('Computed penalty for the rejection')->change();

            // Update rejection_category enum values
            $table->enum('rejection_category', [
                'more_than_24',
                'within_24',
                'advanced_rejection',
                'more_than_6',
                'within_6',
                'after_start'
            ])->comment('Rejection time category')->change();
        });
    }

    public function down()
    {
        Schema::table('rejections', function (Blueprint $table) {
            // Roll back penalty to integer
            $table->integer('penalty')->comment('Computed penalty for the rejection')->change();

            // Roll back to original categories
            $table->enum('rejection_category', [
                'more_than_6',
                'within_6',
                'after_start'
            ])->comment('Rejection time category')->change();
        });
    }
}
