<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deliberation;
use App\Models\Niveau;
use App\Models\SessionExam;
use App\Models\AnneeUniversitaire;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliberationSeeder extends Seeder
{
    /**
     * Crée automatiquement toutes les délibérations nécessaires selon les règles métier
     * Version adaptée pour le modèle Deliberation refactorisé
     */
    public function run()
    {
        $this->command->info('🎯 Début de la création des délibérations...');

        // Récupérer l'année universitaire active
        $anneeActive = AnneeUniversitaire::where('is_active', true)->first();

        if (!$anneeActive) {
            $this->command->error('❌ Aucune année universitaire active trouvée. Veuillez d\'abord exécuter AnneeUniversitaireSeeder.');
            return;
        }

        $this->command->info("📅 Année universitaire active : {$anneeActive->date_start->format('Y')} - {$anneeActive->date_end->format('Y')}");

        // Récupérer tous les niveaux qui peuvent avoir des délibérations
        $niveauxAvecDeliberation = Niveau::where('is_active', true)
            ->where('has_rattrapage', true)
            ->where('is_concours', false)
            ->with('parcours')
            ->get();

        if ($niveauxAvecDeliberation->isEmpty()) {
            $this->command->warn('⚠️ Aucun niveau nécessitant des délibérations trouvé.');
            $this->command->info('💡 Vérifiez que vos niveaux ont has_rattrapage=true et is_concours=false');
            return;
        }

        $this->command->info("🎓 Niveaux concernés : " . $niveauxAvecDeliberation->pluck('nom')->join(', '));

        // Récupérer toutes les sessions de rattrapage pour l'année active
        $sessionsRattrapage = SessionExam::where('annee_universitaire_id', $anneeActive->id)
            ->where('type', 'Rattrapage')
            ->get();

        if ($sessionsRattrapage->isEmpty()) {
            $this->command->error('❌ Aucune session de rattrapage trouvée pour cette année universitaire.');
            return;
        }

        $this->command->info("📝 Sessions de rattrapage : " . $sessionsRattrapage->count());

        $compteurDeliberations = 0;
        $deliberationsCreees = [];

        // Définir les paramètres par défaut pour les délibérations
        // Ces valeurs seront remplacées par des configurations d'application à l'avenir
        $parametresDefaut = $this->obtenirParametresDefaut();

        // Créer les délibérations selon les règles métier
        foreach ($niveauxAvecDeliberation as $niveau) {
            // Adapter les paramètres selon le niveau si nécessaire
            $parametresNiveau = $this->ajusterParametresSelonNiveau($parametresDefaut, $niveau);

            foreach ($sessionsRattrapage as $session) {
                // Vérifier si la délibération existe déjà
                $deliberationExistante = Deliberation::where('niveau_id', $niveau->id)
                    ->where('session_id', $session->id)
                    ->where('annee_universitaire_id', $anneeActive->id)
                    ->first();

                if ($deliberationExistante) {
                    $this->command->warn("⚠️ Délibération déjà existante : {$niveau->nom} - Session {$session->type}");
                    continue;
                }

                // Calculer la date de délibération
                $dateDeliberation = $this->calculerDateDeliberation($session);

                // Créer la délibération avec les nouveaux champs
                $deliberation = Deliberation::create([
                    'niveau_id' => $niveau->id,
                    'session_id' => $session->id,
                    'annee_universitaire_id' => $anneeActive->id,
                    'date_deliberation' => $dateDeliberation,
                    'statut' => Deliberation::STATUT_PROGRAMMEE,
                    'observations' => $this->genererObservationsParDefaut($niveau, $session, $parametresNiveau),

                    // Paramètres de délibération
                    'seuil_admission' => $parametresNiveau['seuil_admission'],
                    'seuil_rachat' => $parametresNiveau['seuil_rachat'],
                    'pourcentage_ue_requises' => $parametresNiveau['pourcentage_ue_requises'],
                    'appliquer_regles_auto' => $parametresNiveau['appliquer_regles_auto'],

                    // Statistiques initialisées
                    'nombre_admis' => 0,
                    'nombre_ajournes' => 0,
                    'nombre_exclus' => 0,
                    'nombre_rachats' => 0
                ]);

                $deliberationsCreees[] = [
                    'niveau' => $niveau->nom,
                    'session' => $session->type,
                    'date' => $dateDeliberation->format('d/m/Y H:i'),
                    'seuil_admission' => $parametresNiveau['seuil_admission'],
                    'seuil_rachat' => $parametresNiveau['seuil_rachat']
                ];

                $compteurDeliberations++;

                $this->command->info("✅ Délibération créée : {$niveau->nom} - {$session->type} - {$dateDeliberation->format('d/m/Y')}");
            }
        }

        // Rapport final et suggestions
        $this->afficherRapportFinal($compteurDeliberations, $deliberationsCreees, $anneeActive);
        $this->afficherSuggestions();
    }

    /**
     * Obtient les paramètres par défaut pour les délibérations
     * Ces paramètres seront ultérieurement configurables dans l'application
     */
    private function obtenirParametresDefaut()
    {
        return [
            'seuil_admission' => 10.00,      // Moyenne minimale pour admission directe
            'seuil_rachat' => 9.75,          // Moyenne minimale pour rachat (admission conditionnelle)
            'pourcentage_ue_requises' => 80,  // % d'UE à valider pour être admis
            'appliquer_regles_auto' => true   // Appliquer automatiquement les règles
        ];
    }

    /**
     * Ajuste les paramètres selon le niveau d'études
     * Permet une personnalisation des règles par niveau
     */
    private function ajusterParametresSelonNiveau($parametresDefaut, $niveau)
    {
        $parametres = $parametresDefaut;

        // Exemples d'ajustements spécifiques par niveau
        switch ($niveau->abr) {
            case 'L2':
                // Plus flexible pour le niveau L2
                $parametres['seuil_rachat'] = 9.50;
                break;
            case 'M2':
                // Plus strict pour le niveau M2
                $parametres['seuil_admission'] = 10.50;
                $parametres['pourcentage_ue_requises'] = 90;
                break;
            case 'D1':
                // Très strict pour le doctorat
                $parametres['seuil_admission'] = 12.00;
                $parametres['seuil_rachat'] = 11.00;
                $parametres['pourcentage_ue_requises'] = 100;
                break;
        }

        return $parametres;
    }

    /**
     * Calcule la date de délibération en fonction de la session
     */
    private function calculerDateDeliberation($session)
    {
        // Commencer 3 jours après la fin de session pour laisser le temps aux corrections
        $dateBase = Carbon::parse($session->date_end)->addDays(3);

        // Ajuster pour éviter les week-ends
        while ($dateBase->isWeekend()) {
            $dateBase->addDay();
        }

        // Programmer à 14h00 par défaut
        $dateBase->setTime(14, 0, 0);

        return $dateBase;
    }

    /**
     * Génère des observations par défaut avec informations sur les critères
     */
    private function genererObservationsParDefaut($niveau, $session, $parametres)
    {
        $observations = "Délibération programmée automatiquement pour le niveau {$niveau->nom} - Session {$session->type}.\n";

        if ($niveau->has_parcours) {
            $observations .= "Niveau avec parcours multiples - Attention aux spécificités de chaque parcours.\n";
        }

        // Inclure les critères spécifiques de la délibération
        $observations .= "Critères de validation :\n";
        $observations .= "- Moyenne minimale pour admission directe : {$parametres['seuil_admission']}\n";
        $observations .= "- Moyenne minimale pour rachat possible : {$parametres['seuil_rachat']}\n";
        $observations .= "- Pourcentage d'UE requises : {$parametres['pourcentage_ue_requises']}%\n";

        $observations .= "\nDécisions possibles : Admis, Admis conditionnellement, Ajourné, Exclu.\n";

        return $observations;
    }

    /**
     * Affiche le rapport de création des délibérations
     */
    private function afficherRapportFinal($compteur, $deliberations, $anneeActive)
    {
        $this->command->info('');
        $this->command->info('📊 ===== RAPPORT DE CRÉATION DES DÉLIBÉRATIONS =====');
        $this->command->info("🎓 Année universitaire : {$anneeActive->date_start->format('Y')} - {$anneeActive->date_end->format('Y')}");
        $this->command->info("📝 Nombre de délibérations créées : {$compteur}");
        $this->command->info('');

        if (!empty($deliberations)) {
            $this->command->info('📋 Détail des délibérations créées :');
            foreach ($deliberations as $delib) {
                $this->command->info(
                    "   • {$delib['niveau']} - {$delib['session']} - {$delib['date']} " .
                    "(Seuils: {$delib['seuil_admission']}/{$delib['seuil_rachat']})"
                );
            }
        }

        $this->command->info('');
        $this->command->info('✅ Toutes les délibérations ont été créées avec succès !');
    }

    /**
     * Affiche des suggestions pour l'utilisation des délibérations
     */
    private function afficherSuggestions()
    {
        $this->command->info('');
        $this->command->info('💡 ===== SUGGESTIONS POUR LA SUITE =====');
        $this->command->info('🔧 Vous pouvez maintenant :');
        $this->command->info('   1. Modifier les dates de délibération via l\'interface d\'administration');
        $this->command->info('   2. Ajuster les seuils d\'admission et de rachat pour chaque délibération');
        $this->command->info('   3. Personnaliser le pourcentage d\'UE requises selon les niveaux');
        $this->command->info('   4. Planifier les membres du jury de délibération');
        $this->command->info('');
        $this->command->info('📝 Note: Les paramètres de délibération sont actuellement définis par niveau.');
        $this->command->info('   À l\'avenir, ils seront configurables dans les paramètres de l\'application.');
        $this->command->info('');
    }
}
