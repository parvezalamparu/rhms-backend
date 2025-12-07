<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('item_code');
            $table->string('item_type');
            $table->string('item_category');
            $table->string('item_subcategory')->nullable();
            $table->integer('low_level')->default(0);
            $table->integer('high_level')->default(0);
            $table->string('company')->nullable();
            $table->string('stored')->nullable();
            $table->string('hsn_or_sac_no')->nullable();
            $table->string('item_unit');
            $table->string('item_subunit')->nullable();
            $table->string('unit_to_subunit')->nullable();
            $table->string('rack_no')->nullable();
            $table->string('shelf_no')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
