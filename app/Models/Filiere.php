<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $table = 'filieres';

    protected $fillable = ['nom'];

    public function classes()
    {
        return $this->hasMany(Classe::class, 'filiere_id');
    }
}