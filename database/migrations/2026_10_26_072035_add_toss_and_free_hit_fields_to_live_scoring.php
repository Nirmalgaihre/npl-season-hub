
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->foreignId('toss_winner_team_id')
                ->nullable()
                ->after('away_team_id')
                ->constrained('teams')
                ->nullOnDelete();

            $table->string('toss_decision', 10)
                ->nullable()
                ->after('toss_winner_team_id');
        });

        Schema::table('live_innings', function (Blueprint $table) {
            $table->boolean('free_hit_next')
                ->default(false)
                ->after('is_current');
        });
    }

    public function down(): void
    {
        Schema::table('live_innings', function (Blueprint $table) {
            $table->dropColumn('free_hit_next');
        });

        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropForeign(['toss_winner_team_id']);
            $table->dropColumn([
                'toss_winner_team_id',
                'toss_decision',
            ]);
        });
    }
};
