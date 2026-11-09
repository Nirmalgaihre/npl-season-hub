<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_table_adjustments', function (Blueprint $table) {
            $table->decimal('manual_points', 7, 3)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('point_table_adjustments', function (Blueprint $table) {
            $table->integer('manual_points')->default(0)->change();
        });
    }
};
