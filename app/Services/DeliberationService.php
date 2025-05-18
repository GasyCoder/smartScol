<?php

namespace App\Services;

use App\Models\Resultat;
use App\Models\Examen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeliberationService
{
    /**
     * Applique la délibération aux résultats validés
     */
    public function appliquerDeliberation($examen_id, $deliberations = [])
    {
        return DB::transaction(function() use ($examen_id, $deliberations) {
            // 1. Marquage des résultats comme publiés
            $count = Resultat::where('examen_id', $examen_id)
                ->where('statut', 'valide')
                ->update([
                    'statut' => 'publie',
                    'updated_at' => now()
                ]);

            // 2. Application des ajustements éventuels du jury
            foreach ($deliberations as $deliberation) {
                if (isset($deliberation['etudiant_id']) && isset($deliberation['points_jury'])) {
                    // Ajuster les notes selon les décisions du jury
                    $resultats = Resultat::where('examen_id', $examen_id)
                        ->where('etudiant_id', $deliberation['etudiant_id'])
                        ->get();

                    foreach ($resultats as $resultat) {
                        // Logique d'ajustement personnalisée selon vos règles
                        // Par exemple, ajouter des points ou modifier une observation
                    }
                }
            }

            return [
                'success' => true,
                'count' => $count,
                'message' => "Délibération appliquée avec succès à $count résultats"
            ];
        });
    }

    /**
     * Calcule les moyennes et décisions pour la délibération
     */
    public function preparerDeliberation($examen_id)
    {
        // Récupération de tous les étudiants concernés
        $resultats = Resultat::where('examen_id', $examen_id)
            ->where('statut', 'valide')
            ->with(['etudiant', 'ec'])
            ->get();

        $resultatsParEtudiant = $resultats->groupBy('etudiant_id');

        $preparation = [];

        foreach ($resultatsParEtudiant as $etudiantId => $resultatsEtudiant) {
            $etudiant = $resultatsEtudiant->first()->etudiant;
            $moyenne = $resultatsEtudiant->avg('note');
            $decision = $moyenne >= 10 ? 'admis' : 'ajourne';

            $preparation[] = [
                'etudiant_id' => $etudiantId,
                'etudiant_nom' => $etudiant->nom,
                'etudiant_prenom' => $etudiant->prenom,
                'matricule' => $etudiant->matricule,
                'moyenne' => $moyenne,
                'decision_initiale' => $decision,
                'resultats_detail' => $resultatsEtudiant->map(function($r) {
                    return [
                        'ec' => $r->ec->nom,
                        'note' => $r->note
                    ];
                })
            ];
        }

        return $preparation;
    }
}
