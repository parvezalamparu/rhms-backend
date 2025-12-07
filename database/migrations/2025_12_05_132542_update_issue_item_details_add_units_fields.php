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
        Schema::table('issue_item_details', function (Blueprint $table) {
            $table->integer('unit_qty')->default(0)->after('exp_date');
            $table->string('unit')->nullable()->after('unit_qty');
            $table->integer('sub_unit_qty')->default(0)->after('unit');
            $table->string('sub_unit')->nullable()->after('sub_unit_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_item_details', function (Blueprint $table) {
            $table->dropColumn(['unit_qty', 'unit', 'sub_unit_qty', 'sub_unit']);
        });
    }
};
