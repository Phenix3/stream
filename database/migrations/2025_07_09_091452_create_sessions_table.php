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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('token');
            $table->text('refresh_token');
            $table->timestamp('expires_at');
            $table->timestamp('refresh_expires_at');
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop, tv
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('expires_at');
            $table->index('refresh_expires_at');
            $table->index('is_active');
            $table->index(['user_id', 'is_active']);
            $table->index(['expires_at', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
