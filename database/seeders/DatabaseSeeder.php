<?php

namespace Database\Seeders;

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
        User::updateOrCreate(
            ['email' => 'admin@bde.fr'],
            [
                'name' => 'Responsable BDE',
                'password' => Hash::make('0702'),
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
    }
}