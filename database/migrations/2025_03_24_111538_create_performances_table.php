<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerformancesTable extends Migration
{
    public function up()
    {
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade'); // Assumes a tenants table exists
            $table->date('date');
            $table->decimal('acceptance', 8, 2);
            $table->decimal('on_time_to_origin', 8, 2);
            $table->decimal('on_time_to_destination', 8, 2);
            $table->decimal('on_time', 8, 2);
            $table->decimal('maintenance_variance_to_spend', 8, 2);
            $table->integer('open_boc');
            $table->boolean('meets_safety_bonus_criteria')->default(false);
            $table->integer('vcr_preventable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('performances');
    }
}
