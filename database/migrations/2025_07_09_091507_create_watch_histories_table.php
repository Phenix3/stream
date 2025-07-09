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
        Schema::create('watch_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            $table->integer('watched_duration')->default(0); // en secondes
            $table->integer('total_duration')->default(0); // en secondes
            $table->boolean('completed')->default(false);
            $table->timestamp('watched_at')->useCurrent();
            $table->integer('last_position')->default(0); // position du dernier arrêt en secondes
            $table->string('device_type')->nullable(); // mobile, tablet, desktop, tv
            $table->string('quality')->nullable(); // 720p, 1080p, etc.
            $table->timestamps();
            
            // Contrainte d'unicité pour éviter les doublons
            $table->unique(['user_id', 'video_id']);
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('video_id');
            $table->index('watched_at');
            $table->index('completed');
            $table->index(['user_id', 'watched_at']);
            $table->index(['user_id', 'completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_histories');
    }
};
