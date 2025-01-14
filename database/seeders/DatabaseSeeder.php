<?php

namespace Database\Seeders;

use App\Models\Classification;
use App\Models\Reward;
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
        $this->createClassifications();
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
        if (Reward::count() > 0) {
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
            Reward::create([
                'label' => $label,
                'description' => $description,
                'order' => $i + 1,
            ]);
        }
    }

    private function createClassifications(): void
    {
        if (Classification::count() > 0) {
            return;
        }

        $classifications = [
            ['Dewasa', 'Misal usia lebih dari 18 tahun'],
            ['Remaja', 'Misal usia antara 12-18 tahun'],
            ['Anak', 'Misal usia antara 6-12 tahun'],
            ['Usia dini', 'Misal usia dibawah 6 tahun'],
        ];

        foreach ($classifications as $i => [$label, $description]) {
            Classification::create([
                'label' => $label,
                'description' => $description,
                'order' => $i + 1,
            ]);
        }
    }
}
