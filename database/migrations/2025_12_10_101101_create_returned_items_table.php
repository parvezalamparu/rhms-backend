<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnedItemsTable extends Migration
{
    public function up()
    {
        Schema::create('returned_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->unique();
            $table->string('returned_id')->unique();
            $table->date('date');
            $table->string('department');
            $table->string('returned_by');
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('returned_items');
    }
}
