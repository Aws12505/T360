<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_of_concern_repair_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('References the repair order');
            $table->foreignId('area_of_concern_id')
                  ->constrained('area_of_concerns')
                  ->onDelete('cascade')
                  ->comment('References the area of concern');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area_of_concern_repair_order');
    }
};