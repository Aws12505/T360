<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRejectionReasonCodesTable extends Migration
{
    public function up()
    {
        Schema::create('rejection_reason_codes', function (Blueprint $table) {
            $table->id();
            // Each reason code is a unique string.
            $table->string('reason_code')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rejection_reason_codes');
    }
}
