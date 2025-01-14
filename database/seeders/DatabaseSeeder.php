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
            ['Juara 1', null],
            ['Juara 2', null],
            ['Juara 3', null],
            ['Juara Harapan 1', null],
            ['Juara Harapan 2', null],
            ['Juara Harapan 3', null],
            ['Juara Favorit', null],
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
            ['Dewasa A', null],
            ['Dewasa B', null],
            ['Dewasa C', null],
            ['Dewasa D', null],
            ['Remaja A', null],
            ['Remaja B', null],
            ['Remaja C', null],
            ['Remaja D', null],
            ['Anak A', null],
            ['Anak B', null],
            ['Anak C', null],
            ['Anak D', null],
            ['Usia dini A', null],
            ['Usia dini B', null],
            ['Usia dini C', null],
            ['Usia dini D', null],
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
