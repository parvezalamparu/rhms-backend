<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();
            $table->string('po_no');
            $table->string('vendor');
            $table->string('generated_by');
            $table->date('date');
            $table->string('note')->nullable();
            $table->string('item_name');
            $table->integer('unit_qty');
            $table->string('unit');
            $table->integer('sub_unit_qty')->nullable();
            $table->string('sub_unit')->nullable();
            $table->enum('status', ['pending', 'received'])->default('pending');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
    }
};
