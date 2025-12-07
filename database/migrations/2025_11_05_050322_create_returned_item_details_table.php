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
        Schema::create('returned_item_details', function (Blueprint $table) {
            $table->id();
            $table->string('returned_id');
            $table->date('date');
            $table->string('department');
            $table->string('returned_by');
            $table->text('note')->nullable();
            $table->string('item_name');
            $table->string('batch_no');
            $table->integer('qty');
            $table->string('reason');
            $table->timestamps();

            $table->foreign('returned_id')->references('returned_id')->on('returned_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returned_item_details');
    }
};
