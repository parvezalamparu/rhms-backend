<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('repair_item_details', function (Blueprint $table) {

            if (Schema::hasColumn('repair_item_details', 'date')) {
                $table->dropColumn('date');
            }

            if (Schema::hasColumn('repair_item_details', 'sent_by')) {
                $table->dropColumn('sent_by');
            }

            if (Schema::hasColumn('repair_item_details', 'note')) {
                $table->dropColumn('note');
            }

            if (Schema::hasColumn('repair_item_details', 'item_name')) {
                $table->dropColumn('item_name');
            }
        });
    }

    public function down()
    {
        Schema::table('repair_item_details', function (Blueprint $table) {

            if (!Schema::hasColumn('repair_item_details', 'date')) {
                $table->date('date')->nullable();
            }

            if (!Schema::hasColumn('repair_item_details', 'sent_by')) {
                $table->string('sent_by')->nullable();
            }

            if (!Schema::hasColumn('repair_item_details', 'note')) {
                $table->text('note')->nullable();
            }

            if (!Schema::hasColumn('repair_item_details', 'item_name')) {
                $table->string('item_name')->nullable();
            }
        });
    }
};
