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
        Schema::create('approved_return_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approved_id');
            $table->string('returned_id');
            $table->date('date');
            $table->string('department');
            $table->string('returned_by');
            $table->string('approved_by');
            $table->string('note')->nullable();
            $table->string('item_name');
            $table->string('batch_no')->nullable();
            $table->integer('qty');
            $table->string('reason')->nullable();
            $table->timestamps();

            // Foreign key constraint (optional)
            $table->foreign('approved_id')
                  ->references('approved_id')
                  ->on('approved_returns')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approved_return_details');
    }
};
