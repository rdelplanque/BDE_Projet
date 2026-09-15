<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $table = 'etudiants';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'date_naissance',
        'telephone',
        'classe_id',
        'points_bonus',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    /**
     * Lignes de participation de cet étudiant (une par événement validé).
     */
    public function participations()
    {
        return $this->hasMany(Participation::class, 'etudiant_id');
    }
}
