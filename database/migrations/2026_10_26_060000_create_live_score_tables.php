<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('live_innings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->unsignedTinyInteger('innings_number')->default(1);
            $table->foreignId('batting_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('bowling_team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('striker')->nullable();
            $table->string('non_striker')->nullable();
            $table->string('current_bowler')->nullable();
            $table->unsignedInteger('total_runs')->default(0);
            $table->unsignedTinyInteger('wickets')->default(0);
            $table->unsignedInteger('legal_balls')->default(0);
            $table->unsignedInteger('extras')->default(0);
            $table->boolean('is_current')->default(true);
            $table->timestamps();
            $table->unique(['fixture_id', 'innings_number']);
        });

        Schema::create('live_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('innings_id')->constrained('live_innings')->cascadeOnDelete();
            $table->unsignedInteger('ball_number');
            $table->string('striker');
            $table->string('non_striker');
            $table->string('bowler');
            $table->unsignedTinyInteger('batter_runs')->default(0);
            $table->unsignedTinyInteger('extra_runs')->default(0);
            $table->enum('extra_type', ['none', 'wide', 'no_ball', 'bye', 'leg_bye', 'penalty'])->default('none');
            $table->boolean('is_wicket')->default(false);
            $table->string('wicket_type')->nullable();
            $table->string('dismissed_batter')->nullable();
            $table->boolean('counts_as_legal_ball')->default(true);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['innings_id', 'ball_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_deliveries');
        Schema::dropIfExists('live_innings');
    }
};
