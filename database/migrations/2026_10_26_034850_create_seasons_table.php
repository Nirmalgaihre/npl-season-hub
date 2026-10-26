<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->unsignedSmallInteger('season_number')->unique();

            $table->string('slug', 120)->unique();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('status', 20)->default('upcoming');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['status', 'season_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};