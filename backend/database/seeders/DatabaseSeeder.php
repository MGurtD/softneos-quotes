<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Order matters because of foreign keys.
     */
    public function run(): void
    {
        // Fixed Faker seed: every migrate:fresh --seed produces the same demo data.
        fake()->seed(2026);

        User::factory()->create([
            'name' => 'Usuario Demo',
            'email' => 'demo@softneos.test',
            'password' => 'password',
        ]);

        $this->call([
            VatTypeSeeder::class,
            ProductReferenceSeeder::class,
            CustomerSeeder::class,
            QuoteSeeder::class,
        ]);
    }
}
