<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Copie extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'examen_id',
        'ec_id',
        'code_anonymat_id',
        'note',
        'saisie_par',
        'note_old',
        'is_checked',
        'commentaire',
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'note_old' => 'decimal:2',
        'is_checked' => 'boolean',
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
        // La relation se fait via examen_id et code_anonymat_id
        return Resultat::where('examen_id', $this->examen_id)
            ->where('code_anonymat_id', $this->code_anonymat_id);
    }

    public function codeAnonymat()
    {
        return $this->belongsTo(CodeAnonymat::class, 'code_anonymat_id');
    }

    public function ec()
    {
        return $this->belongsTo(EC::class);
    }

    // Accesseur pour le code d'anonymat complet
    public function getCodeCompletAttribute()
    {
        return optional($this->codeAnonymat)->code_complet;
    }

    // Extrait le code salle (les lettres) du code_anonymat
    public function getCodeSalleAttribute()
    {
        if (!$this->codeAnonymat) {
            return null;
        }

        preg_match('/([A-Za-z]+)/', $this->codeAnonymat->code_complet, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    // Extrait le numéro (les chiffres) du code_anonymat
    public function getNumeroAttribute()
    {
        if (!$this->codeAnonymat) {
            return null;
        }

        preg_match('/(\d+)$/', $this->codeAnonymat->code_complet, $matches);
        return isset($matches[1]) ? (int)$matches[1] : null;
    }

    // Accesseur pour retrouver l'étudiant via la manchette
    public function getEtudiantAttribute()
    {
        // Trouver la manchette correspondante
        $manchette = $this->findCorrespondingManchette();

        // Retourner l'étudiant si la manchette existe
        return $manchette ? $manchette->etudiant : null;
    }

    public function isAssociated()
    {
        // Vérifier si un résultat existe avec le même code d'anonymat et examen
        return Resultat::where('examen_id', $this->examen_id)
            ->where('code_anonymat_id', $this->code_anonymat_id)
            ->exists();
    }

    // Mise à jour pour trouver la manchette correspondante (tenant compte de l'EC)
    public function findCorrespondingManchette()
    {
        if (!$this->code_anonymat_id) {
            return null;
        }

        return Manchette::where('code_anonymat_id', $this->code_anonymat_id)
                       ->whereHas('codeAnonymat', function($q) {
                           $q->where('ec_id', $this->ec_id);
                       })
                       ->first();
    }
}
