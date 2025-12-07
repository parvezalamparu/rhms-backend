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
        Schema::create('issue_item_details', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no');
            $table->string('requisition_no');
            $table->string('issue_to');
            $table->string('generated_by');
            $table->string('issue_date');
            $table->string('item_name');
            $table->string('batch_no');
            $table->string('exp_date');
            $table->integer('qty');
            $table->integer('amount');
            $table->timestamps();

            $table->foreign('issue_no')
                ->references('issue_no')
                ->on('issue_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_item_details');
    }
};
