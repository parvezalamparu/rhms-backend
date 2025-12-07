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
        Schema::create('repair_item_details', function (Blueprint $table) {
            $table->id();
            $table->string('return_id');
            $table->date('date');
            $table->string('sent_by');
            $table->text('note')->nullable();
            $table->string('item_name');
            $table->string('batch_no');
            $table->integer('qty');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_item_details');
    }
};
