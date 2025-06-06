<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Arafat Hossain',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret'),
        ]);

        // Seeder pour les données de test de la Phase 4 : Sécurité Avancée
        $this->call(Phase4SecurityTestDataSeeder::class);
    }
}
