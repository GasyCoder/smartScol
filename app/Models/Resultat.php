<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultat extends Model
{
    use HasFactory;

    protected $table = 'resultats';

    protected $fillable = [
        'etudiant_id',   // ID de l'étudiant
        'examen_id',     // ID de l'examen (matière)
        'copie_id',      // ID de la copie avec la note
        'manchette_id',  // ID de la manchette avec l'identification
        'note'           // Note finale (reprise de la copie)
    ];

    protected $casts = [
        'note' => 'decimal:2'
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

    public function copie()
    {
        return $this->belongsTo(Copie::class);
    }

    public function manchette()
    {
        return $this->belongsTo(Manchette::class);
    }

    /**
     * Associe une copie et une manchette pour créer un résultat
     */
    public static function fusionnerCopieManchette(Copie $copie, Manchette $manchette)
    {
        // Vérifier que les deux éléments appartiennent au même examen
        if ($copie->examen_id !== $manchette->examen_id) {
            return null;
        }

        // Vérifier que les codes d'anonymat correspondent
        if ($copie->code_anonymat !== $manchette->code_anonymat) {
            return null;
        }

        // Trouver l'étudiant associé à la manchette
        $etudiant = Etudiant::where('matricule', $manchette->matricule_etudiant)->first();
        if (!$etudiant) {
            return null;
        }

        // Créer le résultat
        return self::create([
            'etudiant_id' => $etudiant->id,
            'examen_id' => $copie->examen_id,
            'copie_id' => $copie->id,
            'manchette_id' => $manchette->id,
            'note' => $copie->note
        ]);
    }
}
