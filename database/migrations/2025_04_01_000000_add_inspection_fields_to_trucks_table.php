<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInspectionFieldsToTrucksTable extends Migration
{
    public function up()
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->enum('inspection_status', ['good', 'expired'])->default('good');
            $table->date('inspection_expiry_date')->nullable()->after('inspection_status');
        });
    }

    public function down()
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->dropColumn(['inspection_status', 'inspection_expiry_date']);
        });
    }
}