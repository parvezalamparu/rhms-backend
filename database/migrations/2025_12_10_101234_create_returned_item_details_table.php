<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnedItemDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('returned_item_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->unique();
            $table->string('returned_id')->index(); 
            $table->string('item_name');
            $table->string('batch_no');
            $table->integer('qty')->default(0);
            $table->integer('unit_qty')->default(0);
            $table->string('unit')->nullable();
            $table->integer('sub_unit_qty')->default(0);
            $table->string('sub_unit')->nullable();
            $table->string('reason')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('returned_item_details');
    }
}
