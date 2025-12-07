<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisition_details', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->after('note');

            // If you want foreign key relationship
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('requisition_details', function (Blueprint $table) {
            
            // Drop FK first before dropping column
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');
        });
    }
};
