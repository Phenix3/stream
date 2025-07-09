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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('video_id')->nullable()->constrained('videos')->onDelete('cascade');
            $table->enum('type', ['like', 'watch', 'download', 'share', 'favorite', 'search', 'comment', 'rating']);
            $table->string('platform')->nullable(); // whatsapp, facebook, twitter, etc. pour les partages
            $table->json('metadata')->nullable(); // données supplémentaires spécifiques au type d'activité
            $table->timestamp('timestamp')->useCurrent();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop, tv
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('video_id');
            $table->index('type');
            $table->index('platform');
            $table->index('timestamp');
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'timestamp']);
            $table->index(['type', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
