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
            $fin = Carbon::parse($annee->date_end);

            $isActiveAnnee = $annee->is_active;

            $sessions[] = [
                'annee_universitaire_id' => $annee->id,
                'type' => 'Normale',
                'is_active' => $isActiveAnnee,
                'is_current' => $isActiveAnnee,
                'date_start' => $debut->copy()->addMonths(3)->format('Y-m-d'),
                'date_end' => $debut->copy()->addMonths(4)->format('Y-m-d'),
                'deliberation_appliquee' => false,   // ✅ ajouté
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $sessions[] = [
                'annee_universitaire_id' => $annee->id,
                'type' => 'Rattrapage',
                'is_active' => false,
                'is_current' => $isActiveAnnee,
                'date_start' => $debut->copy()->addMonths(9)->format('Y-m-d'),
                'date_end' => $debut->copy()->addMonths(10)->format('Y-m-d'),
                'deliberation_appliquee' => false,   // ✅ ajouté
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('session_exams')->insert($sessions);

        $this->command->info("✅ Sessions d'examen créées avec succès !");
    }
}
