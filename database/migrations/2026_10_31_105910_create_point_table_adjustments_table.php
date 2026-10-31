
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_table_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('season_id')
                ->constrained('seasons')
                ->cascadeOnDelete();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            $table->unsignedInteger('penalty_points')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['season_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_table_adjustments');
    }
};
