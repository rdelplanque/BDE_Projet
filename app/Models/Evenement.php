<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evenement extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'date_evenement',
        'detail',
        'prix',
        'nombre_place',
        'gain_base',
    ];

    protected function casts(): array
    {
        return [
            'date_evenement' => 'datetime',
            'prix' => 'decimal:2',
            'nombre_place' => 'integer',
            'gain_base' => 'integer',
        ];
    }

    /**
     * Un événement compte plusieurs étudiants participants.
     */
    public function etudiants(): BelongsToMany
    {
        return $this->belongsToMany(Etudiant::class, 'participations')
                    ->withPivot('gain_total')
                    ->withTimestamps();
    }
}