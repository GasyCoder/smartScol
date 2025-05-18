<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultat extends Model
{
    use HasFactory;

    protected $table = 'resultats';

    protected $fillable = [
        'etudiant_id',
        'examen_id',
        'code_anonymat_id',
        'ec_id',
        'note',
        'genere_par',
        'modifie_par',
        'date_generation',
        'date_modification',
        'statut',
        'observation_jury',
        'decision',
        'deliberation_id'
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'date_generation' => 'datetime',
        'date_modification' => 'datetime'
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
        return $query->where('statut', 'provisoire');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopePublie($query)
    {
        return $query->where('statut', 'publie');
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
}
