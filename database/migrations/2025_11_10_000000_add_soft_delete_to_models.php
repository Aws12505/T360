<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteToModels extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // Add deleted_at column to area_of_concerns table
        Schema::table('area_of_concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('area_of_concerns', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add deleted_at column to rejection_reason_codes table
        Schema::table('rejection_reason_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('rejection_reason_codes', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add deleted_at column to delay_codes table
        Schema::table('delay_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('delay_codes', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add deleted_at column to vendors table
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        // Remove deleted_at column from area_of_concerns table
        Schema::table('area_of_concerns', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at column from rejection_reason_codes table
        Schema::table('rejection_reason_codes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at column from delay_codes table
        Schema::table('delay_codes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at column from vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}