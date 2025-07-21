<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliberationConfig;
use App\Models\Niveau;
use App\Models\Parcour;
use App\Models\SessionExam;
use App\Models\AnneeUniversitaire;
use Illuminate\Support\Facades\DB;

class DeliberationConfigSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DeliberationConfig::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ✅ Récupérer l'année universitaire active
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();

        if (!$anneeActive) {
            $this->command->error('❌ Aucune année universitaire active trouvée !');
            return;
        }

        // ✅ Récupérer les sessions de l'année active
        $sessionNormale = SessionExam::where('annee_universitaire_id', $anneeActive->id)
            ->where('type', 'Normale')
            ->first();

        $sessionRattrapage = SessionExam::where('annee_universitaire_id', $anneeActive->id)
            ->where('type', 'Rattrapage')
            ->first();

        if (!$sessionNormale) {
            $this->command->error('❌ Aucune session normale trouvée pour l\'année active !');
            return;
        }

        // ✅ Récupérer les niveaux existants
        $niveaux = Niveau::where('is_active', true)->get();

        if ($niveaux->isEmpty()) {
            $this->command->error('❌ Aucun niveau actif trouvé !');
            return;
        }

        $configs = [];
        $configCount = 0;

        // ✅ CRÉER DES CONFIGURATIONS POUR CHAQUE NIVEAU
        foreach ($niveaux as $niveau) {

            // Récupérer les parcours pour ce niveau (si applicable)
            $parcours = [];
            if ($niveau->has_parcours) {
                $parcours = Parcour::where('niveau_id', $niveau->id)
                    ->where('is_active', true)
                    ->get();
            }

            // ✅ CONFIGURATION POUR SESSION NORMALE
            if ($sessionNormale) {
                if ($parcours->isNotEmpty()) {
                    // Si le niveau a des parcours, créer une config pour chaque parcours
                    foreach ($parcours as $parcour) {
                        $configs[] = $this->createConfig($niveau, $parcour, $sessionNormale);
                        $configCount++;
                    }
                } else {
                    // Pas de parcours, créer une config générale pour le niveau
                    $configs[] = $this->createConfig($niveau, null, $sessionNormale);
                    $configCount++;
                }
            }

            // ✅ CONFIGURATION POUR SESSION RATTRAPAGE
            if ($sessionRattrapage) {
                if ($parcours->isNotEmpty()) {
                    foreach ($parcours as $parcour) {
                        $configs[] = $this->createConfig($niveau, $parcour, $sessionRattrapage);
                        $configCount++;
                    }
                } else {
                    $configs[] = $this->createConfig($niveau, null, $sessionRattrapage);
                    $configCount++;
                }
            }
        }

        // ✅ INSÉRER TOUTES LES CONFIGURATIONS
        foreach ($configs as $config) {
            DeliberationConfig::create($config);
        }

        $this->command->info("✅ {$configCount} configurations de délibération créées avec succès !");

        // ✅ AFFICHER UN RÉSUMÉ
        $this->command->info('📋 Configurations créées :');

        foreach ($niveaux as $niveau) {
            $sessionNormaleConfig = DeliberationConfig::where('niveau_id', $niveau->id)
                ->where('session_id', $sessionNormale->id)
                ->count();

            $sessionRattrapageConfig = $sessionRattrapage ?
                DeliberationConfig::where('niveau_id', $niveau->id)
                    ->where('session_id', $sessionRattrapage->id)
                    ->count() : 0;

            $this->command->line("   - {$niveau->nom}: {$sessionNormaleConfig} config(s) Session Normale" .
                ($sessionRattrapageConfig > 0 ? ", {$sessionRattrapageConfig} config(s) Session Rattrapage" : ""));
        }

        $this->command->info("\n🏥 Logique médecine appliquée :");
        $this->command->line("   - Session 1: 60 crédits obligatoires, note 0 = rattrapage");
        $this->command->line("   - Session 2: 40 crédits = admis, note 0 = exclusion");
    }

    /**
     * ✅ Créer une configuration selon la logique médecine
     */
    private function createConfig($niveau, $parcour, $session)
    {
        // ✅ Logique médecine adaptée selon le niveau
        $creditsS1 = $this->getCreditsS1PourNiveau($niveau);
        $creditsS2 = $this->getCreditsS2PourNiveau($niveau);
        $creditsRedoublement = $this->getCreditsRedoublementPourNiveau($niveau);

        // ✅ Règles note éliminatoire selon le niveau
        $noteEliminatoireS1 = $this->getNoteEliminatoireS1PourNiveau($niveau);
        $noteEliminatoireS2 = $this->getNoteEliminatoireS2PourNiveau($niveau);

        return [
            'niveau_id' => $niveau->id,
            'parcours_id' => $parcour?->id,
            'session_id' => $session->id,

            // ✅ Paramètres selon logique médecine
            'credits_admission_s1' => $creditsS1,
            'credits_admission_s2' => $creditsS2,
            'credits_redoublement_s2' => $creditsRedoublement,

            // ✅ Règles note éliminatoire
            'note_eliminatoire_bloque_s1' => $noteEliminatoireS1,
            'note_eliminatoire_exclusion_s2' => $noteEliminatoireS2,

            // ✅ Statut délibération
            'delibere' => false,
            'date_deliberation' => null,
            'delibere_par' => null,

            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * ✅ Détermine les crédits Session 1 selon le niveau
     */
    private function getCreditsS1PourNiveau($niveau)
    {
        $nomNiveau = strtoupper($niveau->nom);

        // ✅ Logique médecine stricte pour L1/PACES
        if (str_contains($nomNiveau, 'L1') ||
            str_contains($nomNiveau, 'PACES') ||
            str_contains($nomNiveau, 'LICENCE 1') ||
            str_contains($nomNiveau, 'PREMIÈRE ANNÉE')) {
            return 60; // Très strict pour L1 médecine
        }

        // ✅ Peu plus souple pour L2
        if (str_contains($nomNiveau, 'L2') ||
            str_contains($nomNiveau, 'LICENCE 2') ||
            str_contains($nomNiveau, 'DEUXIÈME ANNÉE')) {
            return 55;
        }

        // ✅ Plus souple pour L3 et au-delà
        if (str_contains($nomNiveau, 'L3') ||
            str_contains($nomNiveau, 'LICENCE 3') ||
            str_contains($nomNiveau, 'TROISIÈME ANNÉE')) {
            return 50;
        }

        // ✅ Défaut pour autres niveaux
        return 45;
    }

    /**
     * ✅ Détermine les crédits Session 2 selon le niveau
     */
    private function getCreditsS2PourNiveau($niveau)
    {
        $nomNiveau = strtoupper($niveau->nom);

        // ✅ Logique médecine pour rattrapage
        if (str_contains($nomNiveau, 'L1') ||
            str_contains($nomNiveau, 'PACES') ||
            str_contains($nomNiveau, 'LICENCE 1')) {
            return 40; // Standard médecine
        }

        if (str_contains($nomNiveau, 'L2') ||
            str_contains($nomNiveau, 'LICENCE 2')) {
            return 40;
        }

        if (str_contains($nomNiveau, 'L3') ||
            str_contains($nomNiveau, 'LICENCE 3')) {
            return 35;
        }

        return 30; // Défaut
    }

    /**
     * ✅ Détermine les crédits redoublement selon le niveau
     */
    private function getCreditsRedoublementPourNiveau($niveau)
    {
        $nomNiveau = strtoupper($niveau->nom);

        // ✅ Seuil redoublement médecine
        if (str_contains($nomNiveau, 'L1') ||
            str_contains($nomNiveau, 'PACES') ||
            str_contains($nomNiveau, 'LICENCE 1')) {
            return 20; // Standard médecine L1
        }

        if (str_contains($nomNiveau, 'L2') ||
            str_contains($nomNiveau, 'LICENCE 2')) {
            return 25;
        }

        if (str_contains($nomNiveau, 'L3') ||
            str_contains($nomNiveau, 'LICENCE 3')) {
            return 25;
        }

        return 20; // Défaut
    }

    /**
     * ✅ Détermine si note éliminatoire bloque en S1
     */
    private function getNoteEliminatoireS1PourNiveau($niveau)
    {
        $nomNiveau = strtoupper($niveau->nom);

        // ✅ Très strict pour L1 médecine
        if (str_contains($nomNiveau, 'L1') ||
            str_contains($nomNiveau, 'PACES') ||
            str_contains($nomNiveau, 'LICENCE 1')) {
            return true; // Note 0 = rattrapage automatique
        }

        // ✅ Moins strict pour L2/L3
        if (str_contains($nomNiveau, 'L2') || str_contains($nomNiveau, 'L3')) {
            return false; // Note 0 n'empêche pas admission si assez de crédits
        }

        return false; // Défaut souple
    }

    /**
     * ✅ Détermine si note éliminatoire exclut en S2
     */
    private function getNoteEliminatoireS2PourNiveau($niveau)
    {
        $nomNiveau = strtoupper($niveau->nom);

        // ✅ Très strict pour L1 médecine
        if (str_contains($nomNiveau, 'L1') ||
            str_contains($nomNiveau, 'PACES') ||
            str_contains($nomNiveau, 'LICENCE 1')) {
            return true; // Note 0 en rattrapage = exclusion
        }

        // ✅ Moins strict pour L2/L3
        if (str_contains($nomNiveau, 'L2') || str_contains($nomNiveau, 'L3')) {
            return false; // Note 0 en rattrapage n'exclut pas forcément
        }

        return false; // Défaut souple
    }
}