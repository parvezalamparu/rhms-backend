<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_lists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('purchase_no')->unique(); // Auto generated in model

            $table->string('invoice_no')->nullable();
            $table->string('requisition_no')->nullable();
            $table->string('po_no')->nullable();

            $table->string('vendor');
            $table->string('generated_by');
            $table->date('date');

            $table->string('payment_terms')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_lists');
    }
};
