<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Resultat extends Model
{
    use HasFactory;

    protected $table = 'resultats';

    // Définition des statuts disponibles
    const STATUT_PROVISOIRE = 'provisoire';
    const STATUT_VALIDE = 'valide';
    const STATUT_PUBLIE = 'publie';
    const STATUT_ANNULE = 'annule';

    protected $fillable = [
        'etudiant_id',
        'examen_id',
        'code_anonymat_id',
        'ec_id',
        'note',
        'moyenne_ue',
        'moyenne_generale',
        'genere_par',
        'modifie_par',
        'statut',
        'date_validation',
        'date_publication',
        'status_history',
        'decision',
        'deliberation_id',
        'operation_id'
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'moyenne_ue' => 'decimal:2',
        'moyenne_generale' => 'decimal:2',
        'status_history' => 'array',
        'date_validation' => 'datetime',
        'date_publication' => 'datetime'
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

    public function deliberation()
    {
        return $this->belongsTo(Deliberation::class);
    }

    /**
     * Obtenir les transitions de statut autorisées
     *
     * @return array
     */
    public static function getTransitionsAutorisees()
    {
        return [
            self::STATUT_PROVISOIRE => [self::STATUT_VALIDE, self::STATUT_ANNULE],
            self::STATUT_VALIDE => [self::STATUT_PUBLIE, self::STATUT_PROVISOIRE, self::STATUT_ANNULE],
            self::STATUT_PUBLIE => [self::STATUT_ANNULE],
            self::STATUT_ANNULE => [self::STATUT_PROVISOIRE]
        ];
    }

    /**
     * Vérifie si une transition de statut est autorisée
     *
     * @param string $statutActuel
     * @param string $nouveauStatut
     * @return bool
     */
    public static function transitionAutorisee($statutActuel, $nouveauStatut)
    {
        $transitions = self::getTransitionsAutorisees();
        return in_array($nouveauStatut, $transitions[$statutActuel] ?? []);
    }

    /**
     * Change le statut du résultat
     *
     * @param string $nouveauStatut
     * @param int $userId
     * @return $this
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
        $this->statut = $nouveauStatut;
        $this->status_history = $historique;
        $this->modifie_par = $userId;

        // Mettre à jour les dates spécifiques si nécessaire
        if ($nouveauStatut === self::STATUT_VALIDE) {
            $this->date_validation = now();
        } elseif ($nouveauStatut === self::STATUT_PUBLIE) {
            $this->date_publication = now();
        }

        $this->save();

        // Journaliser le changement
        Log::info('Changement de statut résultat', [
            'resultat_id' => $this->id,
            'de' => $historique[count($historique) - 1]['de'],
            'vers' => $nouveauStatut,
            'user_id' => $userId
        ]);

        return $this;
    }

    /**
     * Indique si ce résultat a été modifié depuis sa création
     */
    public function getEstModifieAttribute()
    {
        return !is_null($this->modifie_par);
    }

    /**
     * Indique si l'étudiant a réussi cette matière
     */
    public function getEstReussieAttribute()
    {
        return $this->note >= 10;
    }

    /**
     * Scopes pour filtrer facilement
     */
    public function scopeProvisoire($query)
    {
        return $query->where('statut', self::STATUT_PROVISOIRE);
    }

    public function scopeValide($query)
    {
        return $query->where('statut', self::STATUT_VALIDE);
    }

    public function scopePublie($query)
    {
        return $query->where('statut', self::STATUT_PUBLIE);
    }

    public function scopeAnnule($query)
    {
        return $query->where('statut', self::STATUT_ANNULE);
    }

    public function scopeReussi($query)
    {
        return $query->where('note', '>=', 10);
    }

    public function scopeEchoue($query)
    {
        return $query->where('note', '<', 10);
    }

    public function scopeAdmis($query)
    {
        return $query->where('decision', 'admis');
    }

    public function scopeAjourne($query)
    {
        return $query->where('decision', 'ajourne');
    }

    public function scopeRattrapage($query)
    {
        return $query->where('decision', 'rattrapage');
    }

    public function scopeExclus($query)
    {
        return $query->where('decision', 'exclus');
    }
}
