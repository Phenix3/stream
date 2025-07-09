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
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            $table->string('download_url')->nullable();
            $table->bigInteger('file_size')->nullable(); // taille en bytes
            $table->string('quality')->default('720p'); // 720p, 1080p, etc.
            $table->string('format')->default('mp4'); // mp4, mkv, etc.
            $table->enum('status', ['pending', 'processing', 'ready', 'downloading', 'completed', 'failed', 'expired'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop, tv
            $table->string('file_path')->nullable(); // chemin local du fichier
            $table->integer('progress')->default(0); // pourcentage de progression (0-100)
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('video_id');
            $table->index('status');
            $table->index('expires_at');
            $table->index('downloaded_at');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
