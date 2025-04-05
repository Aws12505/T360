<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepairOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('ro_number')->comment('RO# with dashes');
            $table->date('ro_open_date')->comment('RO Open Date');
            $table->date('ro_close_date')->nullable()->comment('RO Close Date');
            // Foreign key: reference the trucks table (using its id; you can display truckid from the Truck model)
            $table->unsignedBigInteger('truck_id')->comment('Asset ID: references trucks table');
            $table->foreign('truck_id')->references('id')->on('trucks')->onDelete('cascade');
            // Repairs Made field
            $table->longText('repairs_made')->nullable()->comment('Repairs Made');
            // Vendor: foreign key referencing vendors table
            $table->unsignedBigInteger('vendor_id')->comment('Vendor ID');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->string('wo_number')->nullable()->comment('WO#');
            $table->enum('wo_status', ['Completed', 'Canceled', 'Closed', 'Pending verification', 'Scheduled'])->comment('WO Status');
            $table->string('invoice')->nullable()->comment('Invoice number with dashes');
            $table->decimal('invoice_amount', 15, 2)->nullable()->comment('Invoice Amount');
            $table->boolean('invoice_received')->comment('Did we receive the invoice?');
            $table->boolean('on_qs')->comment('On QS?');
            $table->date('qs_invoice_date')->nullable()->comment('QS Invoice Date');
            $table->boolean('disputed')->comment('Disputed?');
            $table->longText('dispute_outcome')->nullable()->comment('Dispute Outcome');
            $table->unsignedBigInteger('tenant_id')->comment('Tenant ID');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->timestamps();
        });

        // Pivot table for many-to-many: RepairOrder ↔ AreasOfConcern
        Schema::create('area_of_concern_repair_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_order_id');
            $table->unsignedBigInteger('area_of_concern_id');
            $table->foreign('repair_order_id')->references('id')->on('repair_orders')->onDelete('cascade');
            $table->foreign('area_of_concern_id')->references('id')->on('area_of_concerns')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('area_of_concern_repair_order');
        Schema::dropIfExists('repair_orders');
    }
}
