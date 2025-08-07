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
        Schema::create('fish_species', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('scientific_name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('average_weight', 8, 2)->nullable(); // in kg
            $table->decimal('average_length', 8, 2)->nullable(); // in cm
            $table->string('habitat')->nullable();
            $table->string('season')->nullable(); // best fishing season
            $table->string('image_path')->nullable();
            $table->text('fishing_tips')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fish_species');
    }
};
