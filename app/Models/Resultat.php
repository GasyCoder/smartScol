<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultat extends Model
{
    use HasFactory;

    protected $table = 'resultats';

    protected $fillable = [
        'etudiant_id',       // ID de l'étudiant
        'examen_id',         // ID de l'examen
        'code_anonymat_id',  // ID du code d'anonymat
        'note',              // Note finale
        'genere_par',        // Utilisateur qui a généré le résultat
        'statut'             // État du résultat (provisoire, validé, publié)
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

    public function codeAnonymat()
    {
        return $this->belongsTo(CodeAnonymat::class);
    }

    public function utilisateurGeneration()
    {
        return $this->belongsTo(User::class, 'genere_par');
    }

    /**
     * Récupère la copie associée à ce résultat via le code d'anonymat
     */
    public function copie()
    {
        return Copie::where('examen_id', $this->examen_id)
            ->where('code_anonymat_id', $this->code_anonymat_id)
            ->first();
    }

    /**
     * Récupère la manchette associée à ce résultat via le code d'anonymat
     */
    public function manchette()
    {
        return Manchette::where('examen_id', $this->examen_id)
            ->where('code_anonymat_id', $this->code_anonymat_id)
            ->first();
    }
}
