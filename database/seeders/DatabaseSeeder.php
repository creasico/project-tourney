<?php

namespace Database\Seeders;

use App\Models\PrizePool;
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
        $this->createAdminUser();
        $this->createRewards();

        $this->call(ClassificationSeeder::class);
        $this->call(DummySeeder::class);
    }

    private function createAdminUser(): void
    {
        if (User::count() > 0) {
            return;
        }

        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);
    }

    private function createRewards(): void
    {
        if (PrizePool::count() > 0) {
            return;
        }

        $rewards = [
            ['Juara 1', 'Contoh deskripsi Juara 1'],
            ['Juara 2', 'Contoh deskripsi Juara 2'],
            ['Juara 3', 'Contoh deskripsi Juara 3'],
            ['Juara Harapan 1', 'Contoh deskripsi Juara Harapan 1'],
            ['Juara Harapan 2', 'Contoh deskripsi Juara Harapan 2'],
            ['Juara Harapan 3', 'Contoh deskripsi Juara Harapan 3'],
            ['Juara Favorit', 'Contoh deskripsi Juara Favorit'],
        ];

        foreach ($rewards as $i => [$label, $description]) {
            PrizePool::query()->create([
                'label' => $label,
                'description' => $description,
                'order' => $i + 1,
            ]);
        }
    }
}
