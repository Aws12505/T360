<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: AddTenantIdToUsersTable
 *
 * Adds a nullable tenant_id foreign key to the users table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add tenant_id and define the foreign key constraint.
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('References the tenant that the user belongs to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
