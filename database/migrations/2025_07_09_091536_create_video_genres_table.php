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
        Schema::create('video_genres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            $table->foreignId('genre_id')->constrained('genres')->onDelete('cascade');
            $table->timestamps();
            
            // Contrainte d'unicité pour éviter les doublons
            $table->unique(['video_id', 'genre_id']);
            
            // Index pour améliorer les performances
            $table->index('video_id');
            $table->index('genre_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_genres');
    }
};
