
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_deliveries', function (Blueprint $table) {
            $table->string('incoming_batter')->nullable();
            $table->string('striker_after')->nullable();
            $table->string('non_striker_after')->nullable();
            $table->boolean('free_hit_before')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('live_deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'incoming_batter',
                'striker_after',
                'non_striker_after',
                'free_hit_before',
            ]);
        });
    }
};
