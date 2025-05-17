<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackSubjectsTable extends Migration
{
    public function up()
    {
        Schema::create('feedback_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Predefined feedback category');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback_subjects');
    }
}
