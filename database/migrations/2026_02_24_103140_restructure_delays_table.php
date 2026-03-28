<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RestructureDelaysTable extends Migration
{
    public function up()
    {
        // Step 1: Drop delay_codes foreign key and column
        Schema::table('delays', function (Blueprint $table) {
            $table->dropForeign(['delay_code_id']);
            $table->dropColumn('delay_code_id');
        });

        // Step 2: Rename date -> date (change to datetime)
        // and apply all column changes
        Schema::table('delays', function (Blueprint $table) {
            // date → datetime
            $table->dateTime('date')->comment('Date and time of delay event')->change();

            // driver_name → nullable
            $table->string('driver_name', 75)->nullable()->comment('Driver name')->change();

            // delay_category → new enum values, nullable
            DB::statement("ALTER TABLE delays MODIFY COLUMN delay_category ENUM(
                '1_60',
                '61_240',
                '241_600',
                '601_plus'
            ) NULL COMMENT 'Delay category determining the penalty'");

            // penalty stays the same

            // disputed → enum, nullable
            DB::statement("ALTER TABLE delays MODIFY COLUMN disputed ENUM(
                'none',
                'pending',
                'won',
                'lost'
            ) NULL COMMENT 'Dispute status of the delay'");

            // carrier_controllable boolean
            $table->boolean('carrier_controllable')->nullable()->comment('Indicates if the delay is controllable by the carrier');

            // driver_controllable stays but ensure nullable
            $table->boolean('driver_controllable')->nullable()->change();

            // delay_duration integer, nullable
            $table->integer('delay_duration')->nullable()->comment('Duration of the delay in minutes');

            // delay_reason string, nullable
            $table->string('delay_reason')->nullable()->comment('Reason for the delay');

            // load_id string, nullable
            $table->string('load_id')->nullable()->comment('Load ID associated with the delay');

            $table->unique(['load_id', 'delay_type']);
        });

        // Step 3: Drop delay_codes table
        Schema::dropIfExists('delay_codes');
    }

    public function down()
    {
        // Re-create delay_codes table
        Schema::create('delay_codes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::table('delays', function (Blueprint $table) {
            // Revert datetime → date
            $table->date('date')->comment('Date of delay event')->change();

            // Revert driver_name to not nullable
            $table->string('driver_name', 75)->nullable(false)->comment('Driver name')->change();

            // Revert delay_category
            DB::statement("ALTER TABLE delays MODIFY COLUMN delay_category ENUM(
                '1_120',
                '121_600',
                '601_plus',
                '1_60',
                '61_240',
                '241_600'
            ) NOT NULL COMMENT 'Delay category determining the penalty'");

            // Revert disputed → boolean
            DB::statement("ALTER TABLE delays MODIFY COLUMN disputed TINYINT(1) NOT NULL COMMENT 'Indicates if the delay is disputed'");

            // Drop new columns
            $table->dropColumn([
                'carrier_controllable',
                'delay_duration',
                'delay_reason',
                'load_id',
            ]);

            // Re-add delay_code_id
            $table->foreignId('delay_code_id')->constrained()->onDelete('cascade')->comment('References the delay code');
            $table->dropUnique(['load_id', 'delay_type']);
        });
    }
}
