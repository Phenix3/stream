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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            $table->timestamp('favorited_at')->useCurrent();
            $table->timestamps();
            
            // Contrainte d'unicité pour éviter les doublons
            $table->unique(['user_id', 'video_id']);
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('video_id');
            $table->index('favorited_at');
            $table->index(['user_id', 'favorited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
