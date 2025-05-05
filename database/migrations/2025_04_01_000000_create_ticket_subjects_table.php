<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateTicketSubjectsTable
 *
 * Creates the 'ticket_subjects' table to store predefined ticket subjects.
 */
class CreateTicketSubjectsTable extends Migration
{
    public function up()
    {
        Schema::create('ticket_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Predefined ticket subject');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_subjects');
    }
}