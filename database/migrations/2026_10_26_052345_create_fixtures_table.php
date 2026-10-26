<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('season_id')
                ->constrained('seasons')
                ->cascadeOnDelete();

            $table->foreignId('home_team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('away_team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->unsignedInteger('match_number');
            $table->string('match_type', 30)->default('league');

            $table->dateTime('scheduled_at')->nullable();
            $table->string('venue', 180)->nullable();

            $table->string('status', 20)->default('scheduled');

            // Home-team innings
            $table->unsignedSmallInteger('home_runs')->nullable();
            $table->unsignedTinyInteger('home_wickets')->nullable();
            $table->decimal('home_overs', 5, 1)->nullable();

            // Away-team innings
            $table->unsignedSmallInteger('away_runs')->nullable();
            $table->unsignedTinyInteger('away_wickets')->nullable();
            $table->decimal('away_overs', 5, 1)->nullable();

            $table->foreignId('winner_team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            $table->string('player_of_match', 150)->nullable();
            $table->text('result_summary')->nullable();

            $table->boolean('is_result_verified')->default(false);
            $table->foreignId('result_verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['season_id', 'match_number'],
                'fixtures_season_match_number_unique'
            );

            $table->index(['season_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};