<?php

namespace Database\Factories;

use App\Models\Filiere;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classe>
 */
class ClasseFactory extends Factory
{
    public function definition(): array
    {
        // Nom générique du type "Classe A1", "Classe B2", ...
        return [
            'nom' => 'Classe ' . strtoupper($this->faker->unique()->bothify('?#')),
            'filiere_id' => Filiere::factory(),
        ];
    }
}
