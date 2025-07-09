<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'capacite'
    ];

    protected $casts = [
        'capacite' => 'integer'
    ];

    /**
     * Obtient les codes d'anonymat associés à cette salle via les schémas de codage
     */
    public function codesAnonymat()
    {
        return $this->hasManyThrough(
            CodeAnonymat::class,
            'salle_id',     // Clé étrangère sur SchemaCodage
            'id',           // Clé locale de Salle
            'id'            // Clé locale de SchemaCodage
        );
    }

    /**
     * Retourne le nombre total d'étudiants pouvant être accueillis dans cette salle
     */
    public function getCapaciteDisponibleAttribute($date = null)
    {
        // Si une date est spécifiée, on peut vérifier les examens programmés ce jour-là
        if ($date) {
            $examensJour = Examen::whereDate('date', $date)->get();

            // TODO: Logique pour calculer la capacité disponible en fonction des examens
            // Pour l'instant, on retourne simplement la capacité totale
        }

        return $this->capacite;
    }
}
