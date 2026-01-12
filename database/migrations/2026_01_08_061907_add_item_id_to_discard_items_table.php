<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('discard_items', function (Blueprint $table) {

            // Add item_id after return_id
            if (!Schema::hasColumn('discard_items', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('return_id');
            }
        });
    }

    public function down()
    {
        Schema::table('discard_items', function (Blueprint $table) {

            // Remove item_id when rolling back
            if (Schema::hasColumn('discard_items', 'item_id')) {
                $table->dropColumn('item_id');
            }
        });
    }
};
