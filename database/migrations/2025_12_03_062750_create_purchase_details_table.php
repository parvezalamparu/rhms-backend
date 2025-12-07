<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('purchase_no'); // mapping to purchase_lists.purchase_no

            $table->date('purchase_date')->nullable();
            $table->string('generated_by');
            $table->string('vendor');
            $table->string('payment_terms')->nullable();

            $table->string('item_name');
            $table->string('batch_no')->nullable();
            $table->date('exp_date')->nullable();

            $table->integer('qty');
            $table->integer('test_qty')->default(0);

            $table->decimal('rate_per_unit', 15, 2)->default(0);
            $table->decimal('discount_rs', 15, 2)->default(0);
            $table->decimal('discount_percent', 10, 2)->default(0);

            $table->decimal('mrp_per_unit', 15, 2)->default(0);

            $table->decimal('cgst_percent', 10, 2)->default(0);
            $table->decimal('sgst_percent', 10, 2)->default(0);
            $table->decimal('igst_percent', 10, 2)->default(0);

            $table->decimal('total_gst_amount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);

            $table->string('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Reference purchase_no (but not strict foreign key because no integer primary key)
            $table->index('purchase_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_details');
    }
};
