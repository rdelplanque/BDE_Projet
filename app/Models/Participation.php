<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Participation extends Pivot
{
    protected $table = 'participations';

    protected $fillable = [
        'evenement_id',
        'etudiant_id',
        'gain_total',
    ];

    protected function casts(): array
    {
        return [
            'gain_total' => 'integer',
        ];
    }
}