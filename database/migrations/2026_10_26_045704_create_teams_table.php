
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('short_name', 30)->nullable();
            $table->string('slug', 170)->unique();
            $table->string('logo')->nullable();
            $table->string('home_venue', 180)->nullable();
            $table->text('description')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
