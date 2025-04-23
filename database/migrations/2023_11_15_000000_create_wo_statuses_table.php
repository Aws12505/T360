<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWoStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('wo_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Work Order Status Name');
            $table->timestamps();
            
            // Add unique constraint for name and tenant_id
            $table->unique(['name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wo_statuses');
    }

    
}