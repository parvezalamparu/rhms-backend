<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('discard_items', function (Blueprint $table) {
            // Rename item_name → item_id
            if (Schema::hasColumn('discard_items', 'item_name')) {
                $table->renameColumn('item_name', 'item_id');
            }

            // Change type to integer (if needed)
            if (Schema::hasColumn('discard_items', 'item_id')) {
                $table->integer('item_id')->unsigned()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('discard_items', function (Blueprint $table) {
            // Revert rename
            if (Schema::hasColumn('discard_items', 'item_id')) {
                $table->renameColumn('item_id', 'item_name');
            }
        });
    }
};
