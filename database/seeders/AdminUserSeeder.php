<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur administrateur par défaut
        User::updateOrCreate(
            ['email' => 'admin@seledjam.com'],
            [
                'name' => 'Administrateur',
                'email' => 'admin@seledjam.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Utilisateur administrateur créé avec succès !');
        $this->command->info('Email: admin@seledjam.com');
        $this->command->info('Mot de passe: admin123');
    }
}
