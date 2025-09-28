<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function resultatsGeneres()
    {
        return $this->hasMany(ResultatFusion::class, 'genere_par');
    }

    public function resultatsModifies()
    {
        return $this->hasMany(ResultatFusion::class, 'modifie_par');
    }

    public function copiesSaisies()
    {
        return $this->hasMany(Copie::class, 'saisie_par');
    }

    public function copiesModifiees()
    {
        return $this->hasMany(Copie::class, 'modifie_par');
    }

    /**
     * Obtenir les initiales de l'utilisateur.
     *
     * @return string
     */
    public function getInitialsAttribute()
    {
        $nameParts = explode(' ', trim($this->name));
        $initials = '';

        // Prendre la première lettre du premier prénom
        if (!empty($nameParts[0])) {
            $initials .= strtoupper(substr($nameParts[0], 0, 1));
        }

        // Prendre la première lettre du dernier nom (s'il existe)
        if (!empty($nameParts[1])) {
            $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
        } elseif (strlen($nameParts[0]) > 1) {
            // Si une seule partie du nom, prendre les deux premières lettres
            $initials .= strtoupper(substr($nameParts[0], 1, 1));
        }

        return $initials;
    }
}