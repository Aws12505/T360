<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRejectionsTable extends Migration
{
    public function up()
    {
        Schema::create('rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            // Two kinds: block or load rejection.
            $table->enum('rejection_type', ['block', 'load']);
            $table->string('driver_name');
            // Rejection time category:
            // 'more_than_6' (i.e., >6 hours before start time → penalty: 1),
            // 'within_6' (0-6 hours before start time → penalty: 4),
            // 'after_start' (after start time → penalty: 8)
            $table->enum('rejection_category', ['more_than_6', 'within_6', 'after_start']);
            $table->integer('penalty');
            // Foreign key to the rejection_reason_codes table.
            $table->foreignId('reason_code_id')
            ->constrained('rejection_reason_codes')
            ->cascadeOnDelete();
            $table->boolean('disputed');
            // Nullable because the answer might come later
            $table->boolean('driver_controllable')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('rejections');
    }
}
