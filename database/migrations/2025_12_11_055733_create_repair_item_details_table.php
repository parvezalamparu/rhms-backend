<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_item_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Foreign key
            $table->string('return_id');
            $table->foreign('return_id')
                  ->references('return_id')->on('repair_items')
                  ->onDelete('cascade');

            // General info
            $table->date('date');
            $table->string('sent_by');

            // Item details
            $table->string('item_name');
            $table->string('batch_no');
            $table->integer('qty');

            // Units
            $table->integer('unit_qty')->nullable();
            $table->string('unit')->nullable();
            $table->integer('sub_unit_qty')->nullable();
            $table->string('sub_unit')->nullable();

            // Repair cost
            $table->decimal('repair_amount', 10, 2)->nullable();

            // Additional
            $table->text('reason')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_item_details');
    }
};
