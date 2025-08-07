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
        Schema::create('fishing_spot_fish_species', function (Blueprint $table) {
            $table->unsignedBigInteger('fishing_spot_id');
            $table->unsignedBigInteger('fish_species_id');
            $table->timestamps();

            // Primary key
            $table->primary(['fishing_spot_id', 'fish_species_id']);

            // Foreign keys
            $table->foreign('fishing_spot_id')->references('id')->on('fishing_spots')->onDelete('cascade');
            $table->foreign('fish_species_id')->references('id')->on('fish_species')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_spot_fish_species');
    }
};
