<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_table_adjustments', function (Blueprint $table) {
            $table->integer('manual_points')->default(0)->after('penalty_points');
        });
    }

    public function down(): void
    {
        Schema::table('point_table_adjustments', function (Blueprint $table) {
            $table->dropColumn('manual_points');
        });
    }
};