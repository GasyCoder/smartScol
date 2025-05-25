<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResultatFusion extends Model
{
    use HasFactory;

    protected $table = 'resultats_fusion';

    // Statuts du processus de fusion
    const STATUT_VERIFY_1 = 'verify_1';  // Première vérification
    const STATUT_VERIFY_2 = 'verify_2';  // Seconde vérification
    const STATUT_VERIFY_3 = 'verify_3';  // Troisième vérification
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
        'operation_id',
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'status_history' => 'array',
        'date_validation' => 'datetime',
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
            self::STATUT_VERIFY_3 => 'Troisième vérification',
            self::STATUT_VALIDE => 'Validé',
        ];
    }

    /**
     * Transitions autorisées entre statuts
     */
    public static function getTransitionsAutorisees()
    {
        return [
            self::STATUT_VERIFY_1 => [self::STATUT_VERIFY_2],
            self::STATUT_VERIFY_2 => [self::STATUT_VERIFY_1, self::STATUT_VERIFY_3],
            self::STATUT_VERIFY_3 => [self::STATUT_VERIFY_2, self::STATUT_VALIDE],
            self::STATUT_VALIDE => [self::STATUT_VERIFY_3], // Retour possible pour correction
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
        if (!self::transitionAutorisee($this->statut, $nouveauStatut)) {
            throw new \Exception("Transition de statut non autorisée: {$this->statut} → {$nouveauStatut}");
        }

        $historique = $this->status_history ?? [];
        $historique[] = [
            'de' => $this->statut,
            'vers' => $nouveauStatut,
            'user_id' => $userId,
            'date' => now()->toDateTimeString(),
        ];

        $ancienStatut = $this->statut;
        $this->statut = $nouveauStatut;
        $this->status_history = $historique;
        $this->modifie_par = $userId;

        if ($nouveauStatut === self::STATUT_VALIDE && $ancienStatut !== self::STATUT_VALIDE) {
            $this->date_validation = now();
        }

        $this->save();

        Log::info('Changement de statut résultat fusion', [
            'resultat_id' => $this->id,
            'de' => $ancienStatut,
            'vers' => $nouveauStatut,
            'user_id' => $userId,
        ]);

        return $this;
    }

    /**
     * Transfère ce résultat vers la table finale après validation
     */
    public function transfererVersResultatFinal()
    {
        if ($this->statut !== self::STATUT_VALIDE) {
            throw new \Exception("Impossible de transférer un résultat non validé");
        }

        $resultatFinal = ResultatFinal::updateOrCreate(
            [
                'etudiant_id' => $this->etudiant_id,
                'examen_id' => $this->examen_id,
                'ec_id' => $this->ec_id,
            ],
            [
                'code_anonymat_id' => $this->code_anonymat_id,
                'note' => $this->note,
                'genere_par' => $this->genere_par,
                'modifie_par' => $this->modifie_par,
                'fusion_id' => $this->id,
                'date_fusion' => now(),
                'statut' => ResultatFinal::STATUT_EN_ATTENTE,
            ]
        );

        Log::info('Transfert vers résultat final', [
            'fusion_id' => $this->id,
            'final_id' => $resultatFinal->id,
        ]);

        return $resultatFinal;
    }

    /**
     * Indique si cette note est éliminatoire
     */
    public function getEstEliminatoireAttribute()
    {
        return $this->note == 0;
    }

    /**
     * Indique si l'étudiant a réussi cette matière (note >= 10)
     */
    public function getEstReussieAttribute()
    {
        return $this->note >= 10;
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

    public function scopeEliminatoire($query)
    {
        return $query->where('note', '=', 0);
    }

    public function scopeReussi($query)
    {
        return $query->where('note', '>=', 10);
    }

    public function scopeEchoue($query)
    {
        return $query->where('note', '<', 10);
    }

    /**
     * Vérifie les prérequis pour une délibération
     */
    public function verifierPrerequisDeliberation()
    {
        $errors = [];

        if (!$this->examen || !$this->examen->session) {
            $errors[] = "Aucun examen ou session associé à ce résultat";
            return [
                'valide' => false,
                'erreurs' => $errors,
            ];
        }

        // Vérifier la session de rattrapage
        if (!$this->examen->session->isRattrapage()) {
            $errors[] = "La délibération ne peut avoir lieu que pour une session de rattrapage";
        }

        // Vérifier que ce n'est pas un niveau concours
        if ($this->examen->niveau && $this->examen->niveau->is_concours) {
            $errors[] = "Aucune délibération n'est prévue pour les niveaux de concours";
        }

        // Vérifier que tous les résultats sont saisis et vérifiés
        $allResultsValid = ResultatFusion::where('examen_id', $this->examen_id)
            ->where('statut', self::STATUT_VALIDE)
            ->count() === ResultatFusion::where('examen_id', $this->examen_id)->count();

        if (!$allResultsValid) {
            $errors[] = "Tous les résultats doivent être saisis et vérifiés avant la délibération";
        }

        return [
            'valide' => empty($errors),
            'erreurs' => $errors,
        ];
    }

    /**
     * Synchronise les résultats vérifiés à partir des copies
     */
    public function synchroniserResultatsVerifies()
    {
        $copies = Copie::where('examen_id', $this->examen_id)
            ->where('is_checked', true)
            ->get();

        foreach ($copies as $copie) {
            $resultat = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('etudiant_id', $copie->etudiant_id)
                ->where('ec_id', $copie->ec_id)
                ->first();

            if ($resultat) {
                $resultat->note = $copie->note;
                $resultat->statut = self::STATUT_VERIFY_1;
                $resultat->save();
            } else {
                ResultatFusion::create([
                    'examen_id' => $this->examen_id,
                    'etudiant_id' => $copie->etudiant_id,
                    'ec_id' => $copie->ec_id,
                    'code_anonymat_id' => $copie->code_anonymat_id,
                    'note' => $copie->note,
                    'statut' => self::STATUT_VERIFY_1,
                    'genere_par' => Auth::id() ?? 1,
                ]);
            }
        }

        Log::info('Résultats vérifiés synchronisés', [
            'examen_id' => $this->examen_id,
        ]);
    }
}
