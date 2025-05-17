<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeAnonymat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'codes_anonymat';

    protected $fillable = [
        'examen_id',
        'etudiant_id',
        'code_complet',
        'sequence'
    ];

    // Relations
    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function copie()
    {
        return $this->hasOne(Copie::class, 'code_anonymat_id');
    }

    public function manchette()
    {
        return $this->hasOne(Manchette::class, 'code_anonymat_id');
    }

    /**
     * Extracte la séquence numérique du code complet (ex: 'TA1' => 1)
     */
    public function getSequenceFromCode()
    {
        if (preg_match('/(\d+)$/', $this->code_complet, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Avant la sauvegarde, extrait la séquence si non définie
     */
    protected static function booted()
    {
        static::creating(function ($codeAnonymat) {
            if (empty($codeAnonymat->sequence)) {
                $codeAnonymat->sequence = $codeAnonymat->getSequenceFromCode();
            }
        });
    }

    // Extrait le code salle (lettres) du code complet
    public function getCodeSalleAttribute()
    {
        if (preg_match('/^([A-Za-z]+)/', $this->code_complet, $matches)) {
            return $matches[1];
        }
        return null;
    }

    // Extrait le numéro (chiffres) du code complet
    public function getNumeroAttribute()
    {
        if (preg_match('/(\d+)$/', $this->code_complet, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    // Relation avec la salle basée sur le code salle
    public function getSalleAttribute()
    {
        $codeSalle = $this->getCodeSalleAttribute();
        if ($codeSalle) {
            return Salle::where('code_base', $codeSalle)->first();
        }
        return null;
    }
}
