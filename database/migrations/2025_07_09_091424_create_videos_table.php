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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('content_url'); // URL YouTube ou autre
            
            // Relation avec le créateur
            $table->foreignId('creator_id')->constrained('creators')->onDelete('cascade');
            
            // Statistiques et métadonnées
            $table->bigInteger('views')->default(0);
            $table->string('duration')->nullable(); // Format "1h 55m" ou "45m"
            $table->decimal('rating', 3, 1)->default(0); // Note sur 5 avec 1 décimale
            
            // Visibilité et organisation
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->string('season')->nullable(); // "Saison 1", "Film", etc.
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('creator_id');
            $table->index('visibility');
            $table->index(['visibility', 'created_at']);
            $table->index('views');
            $table->index('rating');
            $table->fullText(['title', 'description']); // Pour la recherche textuelle
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
