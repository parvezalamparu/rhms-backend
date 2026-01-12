<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('discard_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('qty');
            $table->integer('sub_unit_qty')->nullable()->after('unit');
            $table->string('sub_unit')->nullable()->after('sub_unit_qty');
        });
    }

    public function down()
    {
        Schema::table('discard_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'sub_unit_qty', 'sub_unit']);
        });
    }
};
