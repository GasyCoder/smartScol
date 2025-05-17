<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnneeUniversitaireSeeder extends Seeder
{
    /**
     * Seed les années universitaires.
     *
     * @return void
     */
    public function run()
    {
        $annees = [
            // Année universitaire 2024-2025 (active)
            [
                'date_start' => '2024-10-01',
                'date_end' => '2025-09-30',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Année universitaire 2025-2026
            [
                'date_start' => '2025-10-01',
                'date_end' => '2026-09-30',
                'is_active' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Année universitaire 2026-2027
            [
                'date_start' => '2026-10-01',
                'date_end' => '2027-09-30',
                'is_active' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Insérer les données
        DB::table('annees_universitaires')->insert($annees);

        $this->command->info('Années universitaires créées avec succès!');
    }
}
