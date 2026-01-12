<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('returned_item_details', function (Blueprint $table) {

            // Add item_id column after returned_id
            if (!Schema::hasColumn('returned_item_details', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('returned_id');
            }
        });
    }

    public function down()
    {
        Schema::table('returned_item_details', function (Blueprint $table) {

            // Drop item_id column on rollback
            if (Schema::hasColumn('returned_item_details', 'item_id')) {
                $table->dropColumn('item_id');
            }
        });
    }
};
