<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliberation extends Model
{
    use HasFactory;

    protected $fillable = [
        'niveau_id',
        'session_id',
        'annee_universitaire_id',
        'date_deliberation',
        'president_jury'
    ];

    protected $casts = [
        'date_deliberation' => 'datetime'
    ];

    /**
     * Relations
     */
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function session()
    {
        return $this->belongsTo(SessionExam::class, 'session_id');
    }

    public function anneeUniversitaire()
    {
        return $this->belongsTo(AnneeUniversitaire::class);
    }

    public function presidentJury()
    {
        return $this->belongsTo(User::class, 'president_jury');
    }

    public function decisions()
    {
        return $this->hasMany(Decision::class);
    }

    /**
     * Relation avec les résultats associés à cette délibération
     */
    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }

    /**
     * Scope pour les délibérations de niveaux supérieurs (L2 à L6)
     */
    public function scopeNiveauxSuperieurs($query)
    {
        return $query->whereHas('niveau', function ($q) {
            $q->avecRattrapage();
        });
    }

    /**
     * Vérifie si tous les résultats sont saisis pour cette délibération
     */
    public function areAllResultatsSaisis()
    {
        // Récupérer les examens concernés
        $examens = Examen::where('niveau_id', $this->niveau_id)
            ->whereHas('session', function ($query) {
                $query->where('id', $this->session_id)
                    ->where('annee_universitaire_id', $this->annee_universitaire_id);
            })
            ->get();

        // Vérifier si tous les examens ont leurs résultats complets
        foreach ($examens as $examen) {
            if (!$examen->isCorrespondanceComplete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Associe cette délibération aux résultats validés correspondants
     */
    public function associerResultatsValides()
    {
        // Récupérer tous les examens concernés par cette délibération
        $examens = Examen::where('niveau_id', $this->niveau_id)
            ->whereHas('session', function ($query) {
                $query->where('id', $this->session_id)
                    ->where('annee_universitaire_id', $this->annee_universitaire_id);
            })
            ->get();

        $count = 0;

        // Pour chaque examen, associer les résultats validés à cette délibération
        foreach ($examens as $examen) {
            $updated = Resultat::where('examen_id', $examen->id)
                ->where('statut', 'valide')
                ->whereNull('deliberation_id')
                ->update([
                    'deliberation_id' => $this->id
                ]);

            $count += $updated;
        }

        return $count;
    }

    /**
     * Calcule les moyennes par étudiant pour cette délibération
     */
    public function calculerMoyennes()
    {
        $moyennes = [];

        // Grouper les résultats par étudiant
        $resultatsParEtudiant = $this->resultats()
            ->with('etudiant', 'ec')
            ->get()
            ->groupBy('etudiant_id');

        foreach ($resultatsParEtudiant as $etudiantId => $resultats) {
            // Calculer la moyenne simple pour l'instant
            // (peut être adapté pour prendre en compte les coefficients)
            $moyenne = $resultats->avg('note');

            $moyennes[$etudiantId] = [
                'etudiant' => $resultats->first()->etudiant,
                'moyenne' => $moyenne,
                'resultats' => $resultats,
                'est_reussi' => $moyenne >= 10
            ];
        }

        return $moyennes;
    }

    /**
     * Publie tous les résultats associés à cette délibération
     */
    public function publierResultats()
    {
        return $this->resultats()
            ->where('statut', 'valide')
            ->update([
                'statut' => 'publie',
                'date_modification' => now()
            ]);
    }
}
