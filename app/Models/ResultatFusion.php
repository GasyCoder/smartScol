<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ResultatFusion extends Model
{
    use HasFactory;

    protected $table = 'resultats_fusion';

    // Statuts du processus de fusion
    const STATUT_VERIFY_1 = 'verify_1';  // Première vérification
    const STATUT_VERIFY_2 = 'verify_2';  // Seconde vérification
    const STATUT_VALIDE = 'valide';      // Validé, prêt pour transfert

    protected $fillable = [
        'etudiant_id',
        'examen_id',
        'code_anonymat_id',
        'ec_id',
        'note',
        'genere_par',
        'modifie_par',
        'etape_fusion',
        'statut',
        'status_history',
        'date_validation',
        'operation_id'
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'status_history' => 'array',
        'date_validation' => 'datetime'
    ];

    /**
     * Relations
     */
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function codeAnonymat()
    {
        return $this->belongsTo(CodeAnonymat::class);
    }

    public function ec()
    {
        return $this->belongsTo(EC::class);
    }

    public function utilisateurGeneration()
    {
        return $this->belongsTo(User::class, 'genere_par');
    }

    public function utilisateurModification()
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }

    /**
     * Libellés des statuts
     */
    public static function getLibellesStatuts()
    {
        return [
            self::STATUT_VERIFY_1 => 'Première vérification',
            self::STATUT_VERIFY_2 => 'Seconde vérification',
            self::STATUT_VALIDE => 'Validé'
        ];
    }

    /**
     * Transitions autorisées entre statuts
     */
    public static function getTransitionsAutorisees()
    {
        return [
            self::STATUT_VERIFY_1 => [self::STATUT_VERIFY_2, self::STATUT_VALIDE],
            self::STATUT_VERIFY_2 => [self::STATUT_VERIFY_1, self::STATUT_VALIDE],
            self::STATUT_VALIDE => [self::STATUT_VERIFY_2] // Retour possible pour correction
        ];
    }

    /**
     * Vérifie si une transition est autorisée
     */
    public static function transitionAutorisee($statutActuel, $nouveauStatut)
    {
        $transitions = self::getTransitionsAutorisees();
        return in_array($nouveauStatut, $transitions[$statutActuel] ?? []);
    }

    /**
     * Change le statut du résultat fusion
     */
    public function changerStatut($nouveauStatut, $userId)
    {
        // Vérifier si la transition est autorisée
        if (!self::transitionAutorisee($this->statut, $nouveauStatut)) {
            throw new \Exception("Transition de statut non autorisée: {$this->statut} → {$nouveauStatut}");
        }

        // Préparer les données historiques
        $historique = $this->status_history ?? [];
        $historique[] = [
            'de' => $this->statut,
            'vers' => $nouveauStatut,
            'user_id' => $userId,
            'date' => now()->toDateTimeString()
        ];

        // Mettre à jour les informations
        $ancienStatut = $this->statut;
        $this->statut = $nouveauStatut;
        $this->status_history = $historique;
        $this->modifie_par = $userId;

        // Définir la date de validation si on valide
        if ($nouveauStatut === self::STATUT_VALIDE && $ancienStatut !== self::STATUT_VALIDE) {
            $this->date_validation = now();
        }

        $this->save();

        // Journaliser le changement
        Log::info('Changement de statut résultat fusion', [
            'resultat_id' => $this->id,
            'de' => $ancienStatut,
            'vers' => $nouveauStatut,
            'user_id' => $userId
        ]);

        return $this;
    }

    /**
     * Transfère ce résultat vers la table finale après validation
     */
    public function transfererVersResultatFinal()
    {
        // Vérifier que le résultat est bien validé
        if ($this->statut !== self::STATUT_VALIDE) {
            throw new \Exception("Impossible de transférer un résultat non validé");
        }

        // Créer ou mettre à jour le résultat final
        $resultatFinal = ResultatFinal::updateOrCreate(
            [
                'etudiant_id' => $this->etudiant_id,
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id
            ],
            [
                'code_anonymat_id' => $this->code_anonymat_id,
                'note' => $this->note,
                'genere_par' => $this->genere_par,
                'modifie_par' => $this->modifie_par,
                'fusion_id' => $this->id,
                'date_fusion' => now()
            ]
        );

        Log::info('Transfert vers résultat final', [
            'fusion_id' => $this->id,
            'final_id' => $resultatFinal->id
        ]);

        return $resultatFinal;
    }

    /**
     * Scopes pour les requêtes
     */
    public function scopePremierVerification($query)
    {
        return $query->where('statut', self::STATUT_VERIFY_1);
    }

    public function scopeSecondeVerification($query)
    {
        return $query->where('statut', self::STATUT_VERIFY_2);
    }

    public function scopeValide($query)
    {
        return $query->where('statut', self::STATUT_VALIDE);
    }
}
