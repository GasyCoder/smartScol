<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NiveauxSeeder extends Seeder
{
    /**
     * Seed les niveaux d'études pour une faculté de médecine.
     *
     * @return void
     */
    public function run()
    {
        $niveaux = [
            // PACES - avec parcours, sous forme de concours, sans rattrapage
            [
                'abr' => 'PACES',
                'nom' => '1er année',
                'has_parcours' => true,
                'has_rattrapage' => false,
                'is_concours' => true,
                'is_active' => true,
            ],

            // Deuxième année - avec parcours, avec rattrapage
            [
                'abr' => 'L2',
                'nom' => '2e année',
                'has_parcours' => true,
                'has_rattrapage' => true,
                'is_concours' => false,
                'is_active' => true,
            ],

            // Troisième année - avec parcours, avec rattrapage
            [
                'abr' => 'L3',
                'nom' => '3e année',
                'has_parcours' => true,
                'has_rattrapage' => true,
                'is_concours' => false,
                'is_active' => true,
            ],

            // Quatrième année (M1) - avec parcours, avec rattrapage
            [
                'abr' => 'M1',
                'nom' => '4e année',
                'has_parcours' => true, // Pour Médecine Générale
                'has_rattrapage' => true,
                'is_concours' => false,
                'is_active' => true,
            ],

            // Cinquième année (M2) - avec parcours, avec rattrapage
            [
                'abr' => 'M2',
                'nom' => '5e année',
                'has_parcours' => true, // Pour Médecine Générale
                'has_rattrapage' => true,
                'is_concours' => false,
                'is_active' => true,
            ],

            // Sixième année (D1) - avec parcours, avec rattrapage
            [
                'abr' => 'D1',
                'nom' => '6e année',
                'has_parcours' => true, // Pour Médecine Générale
                'has_rattrapage' => true,
                'is_concours' => false,
                'is_active' => true,
            ],


        ];

        // Insérer les données
        DB::table('niveaux')->insert($niveaux);

        $this->command->info('Niveaux d\'études créés avec succès!');
    }
}
