<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Reorder columns by dropping and re-adding (safe method)
            $table->string('permission_name')->after('id')->change();
            $table->string('slug')->after('permission_name')->change();
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // (Optional) You can revert order if needed
        });
    }
};
