<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('season_id')
                ->constrained('seasons')
                ->cascadeOnDelete();

            $table->string('name', 150);
            $table->string('category', 80)->default('Official Partner');

            $table->string('logo_path')->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index([
                'season_id',
                'is_active',
                'category',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};