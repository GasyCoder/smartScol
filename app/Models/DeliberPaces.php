<?php
// app/Models/DeliberPaces.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliberPaces extends Model
{
    protected $table = 'deliber_paces';

    protected $fillable = [
        'type',
        'niveau_id',
        'parcours_id',
        'session_exam_id',
        'quota_admission',
        'credits_requis',
        'moyenne_requise',
        'note_eliminatoire',
        'nb_admis',
        'nb_redoublants',
        'nb_exclus',
        'applique_par',
        'applique_at',
        'status',
        'progress',
        'resultats',
        'groupes',
        'statistiques',
        'error_message',
        'duree_secondes'
    ];

    protected $casts = [
        'applique_at' => 'datetime',
        'note_eliminatoire' => 'boolean',
        'duree_secondes' => 'decimal:2',
        'resultats' => 'array',
        'groupes' => 'array',
        'statistiques' => 'array'
    ];

    // Relations
    public function niveau(): BelongsTo
    {
        return $this->belongsTo(Niveau::class);
    }

    public function parcours(): BelongsTo
    {
        return $this->belongsTo(Parcour::class);
    }

    public function sessionExam(): BelongsTo
    {
        return $this->belongsTo(SessionExam::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applique_par');
    }

    // Scopes
    public function scopeSimulations($query)
    {
        return $query->where('type', 'simulation');
    }

    public function scopeDeliberations($query)
    {
        return $query->where('type', 'deliberation');
    }

    public function scopeEnCours($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }
}