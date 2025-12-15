<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SessionExamSeeder extends Seeder
{
    public function run()
    {
        $annees = DB::table('annees_universitaires')->get();

        if ($annees->isEmpty()) {
            $this->command->error("Aucune année universitaire trouvée. Exécutez d'abord le AnneeUniversitaireSeeder.");
            return;
        }

        $sessions = [];

        foreach ($annees as $annee) {
            $debut = Carbon::parse($annee->date_start);
            $isActiveAnnee = $annee->is_active;

            // ✅ Session Normale (ID sera automatiquement 1, 3, 5...)
            $sessions[] = [
                'annee_universitaire_id' => $annee->id,
                'type' => 'Normale',
                'is_active' => $isActiveAnnee,
                'is_current' => $isActiveAnnee,
                'date_start' => $debut->copy()->addMonths(3)->format('Y-m-d'),
                'date_end' => $debut->copy()->addMonths(4)->format('Y-m-d'),
                'deliberation_appliquee' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // ✅ Session Rattrapage (ID sera automatiquement 2, 4, 6...)
            $sessions[] = [
                'annee_universitaire_id' => $annee->id,
                'type' => 'Rattrapage',
                'is_active' => $isActiveAnnee, // ✅ CHANGÉ : Aussi active maintenant
                'is_current' => false,
                'date_start' => $debut->copy()->addMonths(9)->format('Y-m-d'),
                'date_end' => $debut->copy()->addMonths(10)->format('Y-m-d'),
                'deliberation_appliquee' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('session_exams')->insert($sessions);

        $this->command->info("✅ Sessions d'examen créées avec succès !");
        $this->command->info("💡 Les IDs pairs (2, 4, 6...) correspondent aux sessions de rattrapage");
        $this->command->info("💡 Les IDs impairs (1, 3, 5...) correspondent aux sessions normales");
    }
}