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
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }
}