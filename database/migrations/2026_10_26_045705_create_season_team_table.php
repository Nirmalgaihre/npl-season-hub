
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_team', function (Blueprint $table) {
            $table->foreignId('season_id')
                ->constrained('seasons')
                ->cascadeOnDelete();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['season_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_team');
    }
};
