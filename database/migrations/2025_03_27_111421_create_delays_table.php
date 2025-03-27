<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelaysTable extends Migration
{
    public function up()
    {
        Schema::create('delays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            // 'origin' or 'destination'
            $table->enum('delay_type', ['origin', 'destination']);
            $table->string('driver_name');
            // Delay category chosen by user:
            // '1_120' for 1-120 mins (penalty: 1),
            // '121_600' for 121-600 mins (penalty: 2),
            // '601_plus' for 601+ mins (penalty: 4)
            $table->enum('delay_category', ['1_120', '121_600', '601_plus']);
            // Store the penalty as an integer (could be computed on input)
            $table->integer('penalty');
            // Foreign key to the delay_codes table
            $table->foreignId('delay_code_id')->constrained()->cascadeOnDelete();
            $table->boolean('disputed');
            // Nullable because the answer might be provided later
            $table->boolean('driver_controllable')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('delays');
    }
}
