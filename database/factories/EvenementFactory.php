<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evenement>
 */
class EvenementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => ucfirst($this->faker->unique()->words(3, true)),
            'date_evenement' => $this->faker->dateTimeBetween('-1 month', '+4 months'),
            'detail' => $this->faker->paragraph(),
            'prix' => $this->faker->randomFloat(2, 0, 25),
            'nombre_place' => $this->faker->numberBetween(20, 200),
            'gain_base' => $this->faker->numberBetween(0, 50),
        ];
    }
}
