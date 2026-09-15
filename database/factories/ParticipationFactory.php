<?php

namespace Database\Factories;

use App\Models\Etudiant;
use App\Models\Evenement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participation>
 */
class ParticipationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'evenement_id' => Evenement::factory(),
            'etudiant_id' => Etudiant::factory(),
            // Valeur par défaut si la factory est utilisée seule (hors seeder).
            // Le DatabaseSeeder calcule un gain_total plus cohérent à partir de gain_base.
            'gain_total' => $this->faker->numberBetween(0, 50),
        ];
    }
}
