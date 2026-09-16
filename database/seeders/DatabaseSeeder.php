<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Etudiant;
use App\Models\Evenement;
use App\Models\Filiere;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- Utilisateurs init ---
        User::updateOrCreate(
            ['email' => 'admin@bde.fr'],
            [
                'name' => 'Responsable BDE',
                'password' => Hash::make('0702198605062017'),
                'est_admin' => true,
            ],
        );
        User::updateOrCreate(
            ['email' => 'test@bde.fr'],
            [
                'name' => 'Testeur',
                'password' => Hash::make('0702'),
                'est_admin' => false,
            ]
        );

        // --- Filières (vrais noms du projet) ---
        $nomsFilieres = [
            'PPA Business School',
            'PPA Sport',
            'ESGI',
            'ECITV',
            'ISA',
            'EFAB',
            'ENGDE',
            'Ecole W',
            'Maestris BTS',
        ];

        $filieres = collect($nomsFilieres)->map(
            fn (string $nom) => Filiere::firstOrCreate(['nom' => $nom])
        );

        // --- Classes : "<Filière> B1", "B2", "B3", "M1", "M2" pour chaque filière ---
        $suffixesClasses = ['B1', 'B2', 'B3', 'M1', 'M2'];

        $classes = collect();
        foreach ($filieres as $filiere) {
            foreach ($suffixesClasses as $suffixe) {
                $classes->push(
                    Classe::firstOrCreate([
                        'filiere_id' => $filiere->id,
                        'nom' => "{$filiere->nom} {$suffixe}",
                    ])
                );
            }
        }

        // --- Étudiants (répartis aléatoirement dans les classes) ---
        $nbEtudiants = 100;

        $etudiants = collect();
        for ($i = 0; $i < $nbEtudiants; $i++) {
            $etudiants->push(
                Etudiant::factory()->create([
                    'classe_id' => $classes->random()->id,
                ])
            );
        }

        // --- Événements (6) ---
        $evenements = Evenement::factory(6)->create();

        // --- Participations ---
        // Chaque étudiant s'inscrit à 1 à 4 événements différents (au hasard),
        // sans jamais dupliquer une paire evenement/etudiant (contrainte unique en BDD).
        foreach ($etudiants as $etudiant) {
            $nbParticipations = min(random_int(1, 4), $evenements->count());
            // Avec un nombre en argument, random() renvoie toujours une Collection (même pour 1).
            $eventsChoisis = $evenements->random($nbParticipations);

            foreach ($eventsChoisis as $evenement) {
                Participation::firstOrCreate(
                    [
                        'evenement_id' => $evenement->id,
                        'etudiant_id' => $etudiant->id,
                    ],
                    [
                        // Le gain_total part du gain_base de l'événement, +/- un petit bonus.
                        'gain_total' => max(0, $evenement->gain_base + random_int(-5, 20)),
                    ]
                );
            }
        }
    }
}
