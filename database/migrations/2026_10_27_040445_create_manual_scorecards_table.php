
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_scorecards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fixture_id')
                ->constrained('fixtures')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('innings_number');

            $table->foreignId('batting_team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('bowling_team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('total_runs')->default(0);
            $table->unsignedTinyInteger('wickets')->default(0);
            $table->decimal('overs', 5, 1)->default(0);
            $table->unsignedSmallInteger('extras')->default(0);

            $table->json('batters')->nullable();
            $table->json('bowlers')->nullable();

            $table->timestamps();

            $table->unique(
                ['fixture_id', 'innings_number'],
                'manual_scorecards_fixture_innings_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_scorecards');
    }
};
