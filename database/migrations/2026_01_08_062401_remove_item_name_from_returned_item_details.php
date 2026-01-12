<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('returned_item_details', function (Blueprint $table) {

            if (Schema::hasColumn('returned_item_details', 'item_name')) {
                $table->dropColumn('item_name');
            }
        });
    }

    public function down()
    {
        Schema::table('returned_item_details', function (Blueprint $table) {

            if (!Schema::hasColumn('returned_item_details', 'item_name')) {
                $table->string('item_name')->nullable();
            }
        });
    }
};
