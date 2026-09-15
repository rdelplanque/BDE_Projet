<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Filiere>
 */
class FiliereFactory extends Factory
{
    public function definition(): array
    {
        // Nom générique du type "Filière A", "Filière B", ...
        return [
            'nom' => 'Filière ' . strtoupper($this->faker->unique()->lexify('?')),
        ];
    }
}
