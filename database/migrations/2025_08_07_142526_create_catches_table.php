<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fishing_spot_id')->constrained()->onDelete('cascade');
            $table->foreignId('fish_species_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 8, 2)->nullable(); // in kg
            $table->decimal('length', 8, 2)->nullable(); // in cm
            $table->dateTime('date_caught');
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('weather_conditions')->nullable();
            $table->string('bait_used')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catches');
    }
};
