<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParcoursSeeder extends Seeder
{
    /**
     * Seed les parcours d'études pour les différents niveaux.
     *
     * @return void
     */
    public function run()
    {
        // Récupérer les IDs des niveaux
        $niveauPACES = DB::table('niveaux')->where('abr', 'PACES')->first();
        $niveauL2 = DB::table('niveaux')->where('abr', 'L2')->first();
        $niveauL3 = DB::table('niveaux')->where('abr', 'L3')->first();
        $niveauM1 = DB::table('niveaux')->where('abr', 'M1')->first();
        $niveauM2 = DB::table('niveaux')->where('abr', 'M2')->first();
        $niveauD1 = DB::table('niveaux')->where('abr', 'D1')->first();

        if (!$niveauPACES || !$niveauL2 || !$niveauL3 || !$niveauM1 || !$niveauM2 || !$niveauD1) {
            $this->command->error('Un ou plusieurs niveaux n\'existent pas. Veuillez d\'abord exécuter le NiveauxSeeder.');
            return;
        }

        // Liste des parcours
        $parcours = [];

        // Parcours pour PACES
        $parcoursPACES = [
            // Médecine Générale (PACES)
            [
                'abr' => 'MG',
                'nom' => 'Médecine Générale',
                'niveau_id' => $niveauPACES->id,
                'is_active' => true,
            ],

            // Chirurgie Dentaire (uniquement PACES)
            [
                'abr' => 'DENT',
                'nom' => 'Chirurgie Dentaire',
                'niveau_id' => $niveauPACES->id,
                'is_active' => true,
            ],

            // Infirmier Général (PACES)
            [
                'abr' => 'INF-G',
                'nom' => 'Infirmier Général',
                'niveau_id' => $niveauPACES->id,
                'is_active' => true,
            ],

            // Infirmier Anesthésiste (PACES)
            [
                'abr' => 'INF-A',
                'nom' => 'Infirmier Anesthésiste',
                'niveau_id' => $niveauPACES->id,
                'is_active' => true,
            ],

            // Maïeutique (PACES)
            [
                'abr' => 'MAI',
                'nom' => 'Maïeutique',
                'niveau_id' => $niveauPACES->id,
                'is_active' => true,
            ],

            // Vétérinaire (uniquement PACES)
            [
                'abr' => 'VET',
                'nom' => 'Vétérinaire',
                'niveau_id' => $niveauPACES->id,
                'is_active' => true,
            ],

            // Diététique (PACES)
            [
                'abr' => 'DIET',
                'nom' => 'Diététique',
                'niveau_id' => $niveauPACES->id,
                'is_active' => true,
            ],
        ];

        // Ajouter les parcours PACES
        $parcours = array_merge($parcours, $parcoursPACES);

        // Parcours pour L2 (2e année)
        $parcoursL2 = [
            // Médecine Générale (L2)
            [
                'abr' => 'MG',
                'nom' => 'Médecine Générale',
                'niveau_id' => $niveauL2->id,
                'is_active' => true,
            ],

            // Infirmier Général (L2)
            [
                'abr' => 'INF-G',
                'nom' => 'Infirmier Général',
                'niveau_id' => $niveauL2->id,
                'is_active' => true,
            ],

            // Infirmier Anesthésiste (L2)
            [
                'abr' => 'INF-A',
                'nom' => 'Infirmier Anesthésiste',
                'niveau_id' => $niveauL2->id,
                'is_active' => true,
            ],

            // Maïeutique (L2)
            [
                'abr' => 'MAI',
                'nom' => 'Maïeutique',
                'niveau_id' => $niveauL2->id,
                'is_active' => true,
            ],

            // Diététique (L2)
            [
                'abr' => 'DIET',
                'nom' => 'Diététique',
                'niveau_id' => $niveauL2->id,
                'is_active' => true,
            ],
        ];

        // Ajouter les parcours L2
        $parcours = array_merge($parcours, $parcoursL2);

        // Parcours pour L3 (3e année)
        $parcoursL3 = [
            // Médecine Générale (L3)
            [
                'abr' => 'MG',
                'nom' => 'Médecine Générale',
                'niveau_id' => $niveauL3->id,
                'is_active' => true,
            ],

            // Infirmier Général (L3)
            [
                'abr' => 'INF-G',
                'nom' => 'Infirmier Général',
                'niveau_id' => $niveauL3->id,
                'is_active' => true,
            ],

            // Infirmier Anesthésiste (L3)
            [
                'abr' => 'INF-A',
                'nom' => 'Infirmier Anesthésiste',
                'niveau_id' => $niveauL3->id,
                'is_active' => true,
            ],

            // Maïeutique (L3)
            [
                'abr' => 'MAI',
                'nom' => 'Maïeutique',
                'niveau_id' => $niveauL3->id,
                'is_active' => true,
            ],

            // Diététique (L3)
            [
                'abr' => 'DIET',
                'nom' => 'Diététique',
                'niveau_id' => $niveauL3->id,
                'is_active' => true,
            ],
        ];

        // Ajouter les parcours L3
        $parcours = array_merge($parcours, $parcoursL3);

        // Parcours pour M1 (4e année)
        $parcoursM1 = [
            // Médecine Générale (M1)
            [
                'abr' => 'MG',
                'nom' => 'Médecine Générale',
                'niveau_id' => $niveauM1->id,
                'is_active' => true,
            ],
        ];

        // Ajouter les parcours M1
        $parcours = array_merge($parcours, $parcoursM1);

        // Parcours pour M2 (5e année)
        $parcoursM2 = [
            // Médecine Générale (M2)
            [
                'abr' => 'MG',
                'nom' => 'Médecine Générale',
                'niveau_id' => $niveauM2->id,
                'is_active' => true,
            ],
        ];

        // Ajouter les parcours M2
        $parcours = array_merge($parcours, $parcoursM2);

        // Parcours pour D1 (6e année)
        $parcoursD1 = [
            // Médecine Générale (D1)
            [
                'abr' => 'MG',
                'nom' => 'Médecine Générale',
                'niveau_id' => $niveauD1->id,
                'is_active' => true,
            ],
        ];

        // Ajouter les parcours D1
        $parcours = array_merge($parcours, $parcoursD1);

        // Insérer les données
        DB::table('parcours')->insert($parcours);

        $this->command->info('Parcours créés avec succès!');
    }
}
