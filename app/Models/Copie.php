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
        'examen_id',        // ID de l'examen (définit la matière/EC)
        'ec_id',
        'code_anonymat_id', // Référence au code d'anonymat
        'note',             // La note attribuée à cette copie
        'saisie_par'        // L'utilisateur qui a saisi la note
    ];

    protected $casts = [
        'note' => 'decimal:2',
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
        // L'attribut sur CodeAnonymat est code_complet et non pas code
        return optional($this->codeAnonymat)->code_complet;
    }

    // Extrait le code salle (les lettres) du code_anonymat
    public function getCodeSalleAttribute()
    {
        if (!$this->codeAnonymat) {
            return null;
        }

        // Extrait toutes les lettres du code (TA1 => TA)
        preg_match('/([A-Za-z]+)/', $this->codeAnonymat->code_complet, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    // Extrait le numéro (les chiffres) du code_anonymat
    public function getNumeroAttribute()
    {
        if (!$this->codeAnonymat) {
            return null;
        }

        // Extrait tous les chiffres du code (TA1 => 1)
        preg_match('/(\d+)$/', $this->codeAnonymat->code_complet, $matches);
        return isset($matches[1]) ? (int)$matches[1] : null;
    }

    public function etudiant()
    {
        return optional($this->codeAnonymat)->etudiant;
    }

    public function isAssociated()
    {
        // Vérifier si un résultat existe avec le même code d'anonymat et examen
        return Resultat::where('examen_id', $this->examen_id)
            ->where('code_anonymat_id', $this->code_anonymat_id)
            ->exists();
    }


    // Trouve la manchette correspondante avec le même code d'anonymat
    public function findCorrespondingManchette()
    {
        if (!$this->code_anonymat_id) {
            return null;
        }

        return Manchette::where('code_anonymat_id', $this->code_anonymat_id)->first();
    }
}
