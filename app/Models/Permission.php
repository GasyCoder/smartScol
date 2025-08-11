<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // Ajout des nouveaux champs au tableau $fillable
    protected $fillable = [
        'name',
        'guard_name',
        'label',         // Nouveau champ
        'description'    // Nouveau champ
    ];

    // Vous pouvez ajouter des méthodes supplémentaires ici
    // Par exemple, une méthode pour récupérer les permissions par groupe
    public static function getByGroup($group)
    {
        return static::where('name', 'like', $group.'%')->get();
    }
}
