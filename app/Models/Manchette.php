<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manchette extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'manchettes';

    protected $fillable = [
        'examen_id',
        'code_anonymat_id',
        'etudiant_id',
        'saisie_par',
        'date_saisie'
    ];

    // Relations
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function utilisateurSaisie()
    {
        return $this->belongsTo(User::class, 'saisie_par');
    }

    public function resultat()
    {
        // On utilise examen_id et code_anonymat_id pour la relation
        return $this->hasOne(Resultat::class, 'code_anonymat_id', 'code_anonymat_id')
                    ->where('examen_id', $this->examen_id);
    }

    public function codeAnonymat()
    {
        return $this->belongsTo(CodeAnonymat::class, 'code_anonymat_id');
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    // Attributs et méthodes
    public function getCodeAnonymatCompletAttribute()
    {
        return $this->codeAnonymat ? $this->codeAnonymat->code_complet : null;
    }

    public function getCodeSalleAttribute()
    {
        $codeObj = $this->codeAnonymat;
        if ($codeObj && preg_match('/^([A-Za-z]+)/', $codeObj->code_complet, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getNumeroAttribute()
    {
        $codeObj = $this->codeAnonymat;
        if ($codeObj && preg_match('/(\d+)$/', $codeObj->code_complet, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    // Mise à jour pour obtenir l'EC via le code d'anonymat
    public function getEcAttribute()
    {
        return $this->codeAnonymat ? $this->codeAnonymat->ec : null;
    }

    public function getMatriculeEtudiantAttribute()
    {
        return $this->etudiant ? $this->etudiant->matricule : null;
    }

    public function getSalleAttribute()
    {
        $codeSalle = $this->getCodeSalleAttribute();
        if ($codeSalle) {
            return Salle::where('code_base', $codeSalle)->first();
        }
        return null;
    }

    // Vérifie si cette manchette est déjà associée à un résultat
    public function isAssociated()
    {
        if (!$this->code_anonymat_id || !$this->examen_id) {
            return false;
        }

        return Resultat::where('examen_id', $this->examen_id)
                      ->where('code_anonymat_id', $this->code_anonymat_id)
                      ->exists();
    }

    // Mise à jour pour trouver la copie correspondante (tenant compte de l'EC)
    public function findCorrespondingCopie()
    {
        if (!$this->code_anonymat_id) {
            return null;
        }

        return Copie::where('code_anonymat_id', $this->code_anonymat_id)
                   ->where('ec_id', $this->getEcAttribute()->id ?? null)
                   ->first();
    }
}
