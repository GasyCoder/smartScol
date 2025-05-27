<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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
    const STATUT_ANNULE = 'annule';

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
            self::STATUT_VALIDE => [self::STATUT_VERIFY_3],
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
     * Marque le résultat comme vérifié (mise à jour du statut et traçabilité)
     */
    public function marquerCommeVerifie($etape, $userId)
    {
        $nouveauStatut = match($etape) {
            1 => self::STATUT_VERIFY_1,
            2 => self::STATUT_VERIFY_2,
            3 => self::STATUT_VERIFY_3,
            default => throw new \InvalidArgumentException("Étape de vérification invalide: {$etape}")
        };

        $this->statut = $nouveauStatut;
        $this->modifie_par = $userId;

        // Mise à jour de l'historique
        $historique = $this->status_history ?? [];
        $historique[] = [
            'action' => 'verification',
            'etape' => $etape,
            'statut' => $nouveauStatut,
            'user_id' => $userId,
            'date' => now()->toDateTimeString(),
        ];
        $this->status_history = $historique;

        $this->save();

        Log::info('ResultatFusion marqué comme vérifié', [
            'resultat_id' => $this->id,
            'etape' => $etape,
            'statut' => $nouveauStatut,
            'user_id' => $userId
        ]);

        return $this;
    }

    /**
     * Met à jour la note avec traçabilité
     */
    public function mettreAJourNote($nouvelleNote, $userId, $etape = null)
    {
        $ancienneNote = $this->note;
        $this->note = $nouvelleNote;
        $this->modifie_par = $userId;

        // Mise à jour de l'historique
        $historique = $this->status_history ?? [];
        $historique[] = [
            'action' => 'modification_note',
            'ancienne_note' => $ancienneNote,
            'nouvelle_note' => $nouvelleNote,
            'etape' => $etape,
            'user_id' => $userId,
            'date' => now()->toDateTimeString(),
        ];
        $this->status_history = $historique;

        $this->save();

        Log::info('Note ResultatFusion mise à jour', [
            'resultat_id' => $this->id,
            'ancienne_note' => $ancienneNote,
            'nouvelle_note' => $nouvelleNote,
            'user_id' => $userId
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

    public function scopeTroisiemeVerification($query)
    {
        return $query->where('statut', self::STATUT_VERIFY_3);
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

    public function scopeParEtape($query, $etape)
    {
        return $query->where('etape_fusion', $etape);
    }

    public function scopeNecessiteVerification($query)
    {
        return $query->whereIn('statut', [self::STATUT_VERIFY_1, self::STATUT_VERIFY_2]);
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
            // Trouver l'étudiant via la manchette
            $manchette = $copie->findCorrespondingManchette();
            if (!$manchette || !$manchette->etudiant_id) {
                Log::warning('Impossible de trouver l\'étudiant pour la copie', [
                    'copie_id' => $copie->id,
                    'code_anonymat_id' => $copie->code_anonymat_id
                ]);
                continue;
            }

            $resultat = ResultatFusion::where('examen_id', $this->examen_id)
                ->where('etudiant_id', $manchette->etudiant_id)
                ->where('ec_id', $copie->ec_id)
                ->first();

            if ($resultat) {
                $resultat->mettreAJourNote($copie->note, Auth::id(), $this->etape_fusion);
                $resultat->marquerCommeVerifie($this->etape_fusion, Auth::id());
            } else {
                ResultatFusion::create([
                    'examen_id' => $this->examen_id,
                    'etudiant_id' => $manchette->etudiant_id,
                    'ec_id' => $copie->ec_id,
                    'code_anonymat_id' => $copie->code_anonymat_id,
                    'note' => $copie->note,
                    'etape_fusion' => 1,
                    'statut' => self::STATUT_VERIFY_1,
                    'genere_par' => Auth::id() ?? 1,
                ]);
            }
        }

        Log::info('Résultats vérifiés synchronisés', [
            'examen_id' => $this->examen_id,
            'copies_synchronisees' => $copies->count()
        ]);
    }

    /**
     * Marque plusieurs résultats comme vérifiés en lot
     */
    public static function marquerPlusieursCommeVerifies($ids, $etape, $userId)
    {
        $resultats = self::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($resultats as $resultat) {
            try {
                $resultat->marquerCommeVerifie($etape, $userId);
                $count++;
            } catch (\Exception $e) {
                Log::error('Erreur lors du marquage en lot', [
                    'resultat_id' => $resultat->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Marquage en lot terminé', [
            'nb_resultats_traites' => $count,
            'nb_resultats_demandes' => count($ids),
            'etape' => $etape,
            'user_id' => $userId
        ]);

        return $count;
    }

    /**
     * Retourne les statistiques de vérification pour un examen
     */
    public static function getStatistiquesVerification($examenId, $etape = null)
    {
        $query = self::where('examen_id', $examenId);

        if ($etape) {
            $query->where('etape_fusion', $etape);
        }

        $total = $query->count();
        $verify1 = $query->where('statut', self::STATUT_VERIFY_1)->count();
        $verify2 = $query->where('statut', self::STATUT_VERIFY_2)->count();
        $verify3 = $query->where('statut', self::STATUT_VERIFY_3)->count();
        $valide = $query->where('statut', self::STATUT_VALIDE)->count();

        return [
            'total' => $total,
            'verify_1' => $verify1,
            'verify_2' => $verify2,
            'verify_3' => $verify3,
            'valide' => $valide,
            'en_cours' => $verify1 + $verify2,
            'termines' => $verify3 + $valide,
            'pourcentage_completion' => $total > 0 ? round((($verify3 + $valide) / $total) * 100, 1) : 0
        ];
    }

    /**
     * Restaure les résultats annulés à l'état en attente
     *
     * @param int $examenId
     * @return array
     */
    public function revenirValidation($examenId)
    {
        try {
            DB::beginTransaction();

            $resultats = ResultatFinal::where('examen_id', $examenId)
                ->where('statut', ResultatFinal::STATUT_ANNULE)
                ->get();

            if ($resultats->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucun résultat annulé à restaurer.',
                ];
            }

            // Récupérer les IDs des résultats fusionnés associés
            $fusionIds = $resultats->pluck('fusion_id')->filter()->unique()->toArray();

            // Restaurer les résultats finaux
            $updatedCount = 0;
            foreach ($resultats as $resultat) {
                $statusHistory = $resultat->status_history ?? [];
                $statusHistory[] = [
                    'de' => $resultat->statut,
                    'vers' => ResultatFinal::STATUT_EN_ATTENTE,
                    'user_id' => Auth::id(),
                    'date' => now()->toDateTimeString(),
                ];

                $resultat->update([
                    'statut' => ResultatFinal::STATUT_EN_ATTENTE,
                    'motif_annulation' => null,
                    'date_annulation' => null,
                    'annule_par' => null,
                    'date_reactivation' => now(),
                    'reactive_par' => Auth::id(),
                    'status_history' => $statusHistory,
                ]);

                $updatedCount++;
            }

            // Restaurer les résultats fusionnés
            if (!empty($fusionIds)) {
                ResultatFusion::whereIn('id', $fusionIds)
                    ->update([
                        'statut' => ResultatFusion::STATUT_VALIDE,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            Log::info('Retour à l\'état en attente effectué', [
                'examen_id' => $examenId,
                'resultats_restaures' => $updatedCount,
            ]);

            return [
                'success' => true,
                'message' => "Retour à l'état en attente effectué. $updatedCount résultats restaurés.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du retour à l\'état en attente', [
                'examen_id' => $examenId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur lors du retour : ' . $e->getMessage(),
            ];
        }
    }
}