<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_item_details', function (Blueprint $table) {

            // Add new fields in correct order after exp_date
            $table->integer('unit_qty')->nullable()->after('exp_date');
            $table->string('unit')->nullable()->after('unit_qty');
            $table->integer('sub_unit_qty')->nullable()->after('unit');
            $table->string('sub_unit')->nullable()->after('sub_unit_qty');

            // Change qty to string and make nullable
            $table->string('qty')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('issue_item_details', function (Blueprint $table) {

            // Remove newly added unit fields
            $table->dropColumn(['unit_qty', 'unit', 'sub_unit_qty', 'sub_unit']);

            // Revert qty back to numeric if rollback happens
            $table->decimal('qty', 10, 2)->nullable()->change();
        });
    }
};
