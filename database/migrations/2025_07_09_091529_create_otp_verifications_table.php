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
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('phone'); // numéro de téléphone au format international
            $table->string('otp_code'); // code à 6 chiffres
            $table->string('temp_user_id')->nullable(); // ID temporaire pour les nouveaux utilisateurs
            $table->timestamp('expires_at'); // expiration du code
            $table->timestamp('verified_at')->nullable(); // quand le code a été vérifié
            $table->integer('attempts')->default(0); // nombre de tentatives
            $table->integer('max_attempts')->default(3); // maximum de tentatives autorisées
            $table->enum('status', ['pending', 'verified', 'expired', 'failed', 'blocked'])->default('pending');
            $table->enum('method', ['whatsapp', 'sms', 'call'])->default('whatsapp');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('phone');
            $table->index('temp_user_id');
            $table->index('expires_at');
            $table->index('status');
            $table->index('method');
            $table->index(['phone', 'status']);
            $table->index(['expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
