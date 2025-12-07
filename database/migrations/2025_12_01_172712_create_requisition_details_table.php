<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisition_details', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_no');
            $table->string('uuid')->unique();
            $table->date('date');
            $table->string('generated_by');
            $table->string('department');
            $table->string('note')->nullable();
            $table->string('item_name');
            $table->string('req_qty');
            $table->string('issued_unit')->default();
            $table->string('total')->nullable();
            $table->string('relation')->nullable();
            $table->enum('status', ['pending', 'issued'])->default('pending');
            $table->timestamps();

            $table->foreign('requisition_no')
                ->references('requisition_no')
                ->on('requisitions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_details');
    }
};
