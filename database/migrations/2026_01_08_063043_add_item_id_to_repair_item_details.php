<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('repair_item_details', function (Blueprint $table) {

            // Add item_id after repair_id
            if (!Schema::hasColumn('repair_item_details', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('return_id');
            }

        });
    }

    public function down()
    {
        Schema::table('repair_item_details', function (Blueprint $table) {

            if (Schema::hasColumn('repair_item_details', 'item_id')) {
                $table->dropColumn('item_id');
            }

        });
    }
};
