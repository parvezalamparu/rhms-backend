<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_lists', function (Blueprint $table) {

            if (!Schema::hasColumn('purchase_lists', 'purchase_type')) {
                $table->string('purchase_type', 50)
                      ->after('requisition_no');
            }

            if (!Schema::hasColumn('purchase_lists', 'discount_type')) {
                $table->enum('discount_type', ['rs', 'percent'])
                      ->nullable()
                      ->after('payment_terms');
            }

            if (!Schema::hasColumn('purchase_lists', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)
                      ->default(0)
                      ->after('discount_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_lists', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_type',
                'discount_type',
                'discount_value',
            ]);
        });
    }
};
