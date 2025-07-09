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
        Schema::table('users', function (Blueprint $table) {
            // Ajouter les nouveaux champs pour l'authentification
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('account_id')->nullable()->after('avatar');
            
            // Ajouter les champs de vérification
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            
            // Ajouter les statistiques utilisateur
            $table->integer('videos_watched')->default(0)->after('phone_verified_at');
            $table->integer('total_watch_time')->default(0)->after('videos_watched'); // en secondes
            
            // Ajouter les index pour améliorer les performances
            $table->index('phone');
            $table->index('account_id');
            $table->index(['email', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les index
            $table->dropIndex(['users_phone_index']);
            $table->dropIndex(['users_account_id_index']);
            $table->dropIndex(['users_email_phone_index']);
            
            // Supprimer les colonnes ajoutées
            $table->dropColumn([
                'phone',
                'avatar',
                'account_id',
                'phone_verified_at',
                'videos_watched',
                'total_watch_time'
            ]);
        });
    }
};
