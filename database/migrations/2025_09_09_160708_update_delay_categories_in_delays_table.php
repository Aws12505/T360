<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDelayCategoriesInDelaysTable extends Migration
{
    public function up()
    {
        Schema::table('delays', function (Blueprint $table) {
            // Modify the enum column
            $table->enum('delay_category', [
                '1_120',
                '121_600',
                '601_plus',
                '1_60',
                '61_240',
                '241_600'
            ])->comment('Delay category determining the penalty')->change();
        });
    }

    public function down()
    {
        Schema::table('delays', function (Blueprint $table) {
            // Rollback to original categories
            $table->enum('delay_category', [
                '1_120',
                '121_600',
                '601_plus'
            ])->comment('Delay category determining the penalty')->change();
        });
    }
}
