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
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->integer('subscriber_count')->default(0);
            $table->text('description')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('name');
            $table->index('subscriber_count');
            $table->index('verified');
            $table->fullText('name'); // Pour la recherche textuelle
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creators');
    }
};
