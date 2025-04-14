<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrucksTable extends Migration
{
    public function up()
    {
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            // User-entered truckid
            $table->integer('truckid');
            $table->enum('type', ['daycab', 'sleepercab']);
            $table->enum('make', ['international', 'kenworth', 'peterbilt', 'volvo', 'freightliner']);
            $table->enum('fuel', ['cng', 'diesel']);
            $table->integer('license');
            $table->string('vin')->unique();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            // Replace boolean is_active with enum status
            $table->enum('status', ['active', 'inactive', 'Returned to AMZ'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trucks');
    }
}