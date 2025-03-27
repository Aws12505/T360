<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelayCodesTable extends Migration
{
    public function up()
    {
        Schema::create('delay_codes', function (Blueprint $table) {
            $table->id();
            // Each delay code is a unique string.
            $table->string('code')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delay_codes');
    }
}
