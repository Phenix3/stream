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
        Schema::create('video_actors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            $table->foreignId('actor_id')->constrained('actors')->onDelete('cascade');
            $table->timestamps();
            
            // Contrainte d'unicité pour éviter les doublons
            $table->unique(['video_id', 'actor_id']);
            
            // Index pour améliorer les performances
            $table->index('video_id');
            $table->index('actor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_actors');
    }
};
