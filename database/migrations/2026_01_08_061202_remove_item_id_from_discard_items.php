<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('discard_items', function (Blueprint $table) {

            // Remove the column
            if (Schema::hasColumn('discard_items', 'item_id')) {
                $table->dropColumn('item_id');
            }
        });
    }

    public function down()
    {
        Schema::table('discard_items', function (Blueprint $table) {

            // Add it back if rolling back
            if (!Schema::hasColumn('discard_items', 'item_id')) {
                $table->integer('item_id')->nullable();
            }
        });
    }
};
