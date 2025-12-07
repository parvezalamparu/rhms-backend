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
        Schema::create('discard_items', function (Blueprint $table) {
            $table->id();
            $table->string('return_id');
            $table->string('item_name');
            $table->string('batch_no');
            $table->string('returned_department');
            $table->string('return_by');
            $table->integer('qty');
            $table->string('discarded_by');
            $table->text('discarded_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discard_items');
    }
};
