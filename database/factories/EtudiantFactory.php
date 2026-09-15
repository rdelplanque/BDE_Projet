<?php

namespace Database\Factories;

use App\Models\Classe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Etudiant>
 */
class EtudiantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'date_naissance' => $this->faker->dateTimeBetween('-25 years', '-18 years'),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->optional(0.85)->numerify('06########'),
            'classe_id' => Classe::factory(),
        ];
    }
}
