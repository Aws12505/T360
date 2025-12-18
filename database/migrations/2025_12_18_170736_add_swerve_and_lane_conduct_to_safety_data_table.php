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
        Schema::table('safety_data', function (Blueprint $table) {
            $table->decimal('swerve',10,2)->nullable()->comment('Swerve Events')->after('weaving_gz_impact');
            $table->decimal('lane_conduct',10,2)->nullable()->comment('Lane Conduct Events')->after('roadside_parking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safety_data', function (Blueprint $table) {
            $table->dropColumn('swerve');
            $table->dropColumn('lane_conduct');
        });
    }
};
