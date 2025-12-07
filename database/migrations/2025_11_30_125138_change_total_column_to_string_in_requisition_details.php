<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('requisition_details', function (Blueprint $table) {
            $table->string('total')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('requisition_details', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->nullable()->change();
        });
    }
};