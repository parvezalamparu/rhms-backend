<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('purchase_details', function (Blueprint $table) {

        if (!Schema::hasColumn('purchase_details', 'unit')) {
            $table->string('unit', 50)->nullable()->after('qty');
        }

        if (!Schema::hasColumn('purchase_details', 'sub_unit_qty')) {
            $table->decimal('sub_unit_qty', 10, 2)->nullable()->after('unit');
        }

        if (!Schema::hasColumn('purchase_details', 'sub_unit')) {
            $table->string('sub_unit', 50)->nullable()->after('sub_unit_qty');
        }
    });
}


    public function down(): void
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->dropColumn(['unit', 'sub_unit_qty', 'sub_unit']);
        });
    }
};
