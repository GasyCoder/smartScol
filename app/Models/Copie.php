<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copie extends Model
{
    use HasFactory;

    protected $fillable = [
        'examen_id',      // ID de l'examen (définit la matière/EC)
        'code_anonymat',  // Code complet (ex: TA1, TA2, SA1, SA2)
        'note',           // La note attribuée à cette copie
        'saisie_par'      // L'utilisateur qui a saisi la note
    ];

    protected $casts = [
        'note' => 'decimal:2',
    ];

    /**
     * Relations
     */
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
        return $this->hasOne(Resultat::class);
    }

    /**
     * Attributs et méthodes
     */
    // Extrait le code salle (les lettres) du code_anonymat
    public function getCodeSalleAttribute()
    {
        return preg_replace('/[0-9]+/', '', $this->code_anonymat);
    }

    // Extrait le numéro (les chiffres) du code_anonymat
    public function getNumeroAttribute()
    {
        preg_match('/([0-9]+)/', $this->code_anonymat, $matches);
        return $matches[0] ?? null;
    }

    // Récupérer la matière (EC) à travers l'examen
    public function getEcAttribute()
    {
        return $this->examen->ec;
    }

    // Récupérer la salle correspondant au code
    public function getSalleAttribute()
    {
        $code_salle = $this->getCodeSalleAttribute();
        return Salle::where('code', $code_salle)->first();
    }

    // Vérifie si cette copie est déjà associée à un résultat
    public function isAssociated()
    {
        return $this->resultat()->exists();
    }

    // Trouve la manchette correspondante avec le même code d'anonymat
    public function findCorrespondingManchette()
    {
        return Manchette::where('examen_id', $this->examen_id)
            ->where('code_anonymat', $this->code_anonymat)
            ->first();
    }
}

