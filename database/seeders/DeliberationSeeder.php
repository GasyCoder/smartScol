<?php

namespace Database\Seeders;

use App\Models\AnneeUniversitaire;
use App\Models\Deliberation;
use App\Models\Etudiant;
use App\Models\Examen;
use App\Models\Niveau;
use App\Models\ResultatFinal;
use App\Models\SessionExam;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DeliberationSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🎯 Création des délibérations...');

        // Récupérer l'utilisateur superadmin créé par DatabaseSeeder
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $this->command->error('❌ Utilisateur admin@example.com non trouvé. Vérifiez DatabaseSeeder.');
            Log::error('DeliberationSeeder: Utilisateur admin@example.com non trouvé.');
            return;
        }

        // Vérifier l'année universitaire active
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();
        if (!$anneeActive) {
            $this->command->error('❌ Aucune année universitaire active trouvée.');
            Log::error('DeliberationSeeder: Aucune année universitaire active.');
            return;
        }

        // Récupérer les niveaux avec rattrapage
        $niveaux = Niveau::where('is_active', true)
            ->where('has_rattrapage', true)
            ->where('is_concours', false)
            ->get();

        if ($niveaux->isEmpty()) {
            $this->command->warn('⚠️ Aucun niveau avec rattrapage trouvé.');
            Log::warning('DeliberationSeeder: Aucun niveau avec has_rattrapage=true et is_concours=false.');
            return;
        }

        // Récupérer les sessions de rattrapage
        $sessionsRattrapage = SessionExam::where('annee_universitaire_id', $anneeActive->id)
            ->where('type', 'Rattrapage')
            ->get();

        if ($sessionsRattrapage->isEmpty()) {
            $this->command->error('❌ Aucune session de rattrapage trouvée.');
            Log::error('DeliberationSeeder: Aucune session de rattrapage.');
            return;
        }

        $compteurDeliberations = 0;

        foreach ($niveaux as $niveau) {
            foreach ($sessionsRattrapage as $session) {
                // Récupérer les examens pour ce niveau et cette session
                $examens = Examen::where('niveau_id', $niveau->id)
                    ->where('session_id', $session->id)
                    ->with('ecs')
                    ->get();

                if ($examens->isEmpty()) {
                    $this->command->warn("⚠️ Aucun examen pour {$niveau->nom} dans la session {$session->type}.");
                    Log::warning("DeliberationSeeder: Aucun examen pour niveau_id={$niveau->id}, session_id={$session->id}.");
                    continue;
                }

                foreach ($examens as $examen) {
                    // Vérifier si une délibération existe déjà
                    if (Deliberation::where('niveau_id', $niveau->id)
                        ->where('session_id', $session->id)
                        ->where('examen_id', $examen->id)
                        ->exists()
                    ) {
                        $this->command->info("ℹ️ Délibération déjà existante pour {$niveau->nom} - Examen ID {$examen->id}.");
                        continue;
                    }

                    // Appliquer les paramètres par défaut du modèle
                    $params = Deliberation::getDefaultParamsForNiveau($niveau);

                    // Créer la délibération
                    $deliberation = Deliberation::create([
                        'niveau_id' => $niveau->id,
                        'session_id' => $session->id,
                        'examen_id' => $examen->id,
                        'annee_universitaire_id' => $anneeActive->id,
                        'date_deliberation' => Carbon::parse($session->date_end)->addDays(3)->startOfDay()->addHours(14),
                        'statut' => Deliberation::STATUT_PROGRAMMEE,
                        'seuil_admission' => $params['seuil_admission'],
                        'seuil_rachat' => $params['seuil_rachat'],
                        'pourcentage_ue_requises' => $params['pourcentage_ue_requises'],
                        'appliquer_regles_auto' => $params['appliquer_regles_auto'],
                        'observations' => "Délibération pour {$niveau->nom} - Session {$session->type} - Examen: " . $examen->ecs->pluck('nom')->join(', '),
                    ]);

                    // Créer des données de test
                    $this->creerDonneesTest($deliberation, $examen, $adminUser->id);

                    $compteurDeliberations++;
                    $this->command->info("✅ Délibération créée : {$niveau->nom} - Examen ID {$examen->id} - {$session->type}");
                }
            }
        }

        $this->command->info("📊 Résultat : {$compteurDeliberations} délibérations créées.");
    }

    private function creerDonneesTest($deliberation, $examen, $userId)
    {
        // Récupérer jusqu'à 5 étudiants actifs pour ce niveau
        $etudiants = Etudiant::where('niveau_id', $deliberation->niveau_id)
            ->where('is_active', true)
            ->take(5)
            ->get();

        if ($etudiants->isEmpty()) {
            $this->command->warn("⚠️ Aucun étudiant actif pour {$deliberation->niveau->nom}.");
            Log::warning("DeliberationSeeder: Aucun étudiant pour niveau_id={$deliberation->niveau_id}.");
            return;
        }

        // Récupérer les ECs de l'examen
        $ecs = $examen->ecs;

        if ($ecs->isEmpty()) {
            $this->command->warn("⚠️ Aucun EC pour l'examen ID {$examen->id}.");
            Log::warning("DeliberationSeeder: Aucun EC pour examen_id={$examen->id}.");
            return;
        }

        foreach ($etudiants as $etudiant) {
            foreach ($ecs as $ec) {
                // Créer un résultat final pour chaque étudiant et EC
                ResultatFinal::create([
                    'deliberation_id' => $deliberation->id,
                    'examen_id' => $examen->id,
                    'etudiant_id' => $etudiant->id,
                    'ec_id' => $ec->id,
                    'note' => rand(0, 2000) / 100, // Note aléatoire entre 0 et 20
                    'statut' => ResultatFinal::STATUT_EN_ATTENTE,
                    'genere_par' => $userId,
                ]);
            }
        }

        $this->command->info("✅ Données de test créées pour la délibération ID {$deliberation->id}.");
    }
}
