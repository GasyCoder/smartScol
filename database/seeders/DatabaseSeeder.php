<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Appeler d'abord le seeder des rôles et permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // Créer les utilisateurs de test avec leurs rôles

        // Superadmin
        $superadmin = User::create([
            'name' => 'Admin Super',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $superadmin->assignRole('superadmin');

        // Enseignant
        $enseignant = User::create([
            'name' => 'Dupont',
            'username' => 'enseignant',
            'email' => 'enseignant@example.com',
            'password' => Hash::make('password'),
        ]);
        $enseignant->assignRole('enseignant');

        // Secrétaire
        $secretaire = User::create([
            'name' => 'Martin',
            'username' => 'secretaire',
            'email' => 'secretaire@example.com',
            'password' => Hash::make('password'),
        ]);
        $secretaire->assignRole('secretaire');
    }
}
