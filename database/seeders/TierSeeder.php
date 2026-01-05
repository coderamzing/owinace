<?php

namespace Database\Seeders;

use App\Models\Tier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'id' => 1,
                'title' => 'Starter',
                'price' => 29.00,
                'special_price' => null,
                'description' => 'Perfect for small teams',
                'is_active' => true,
                'max_members' => 10,
                'max_storage' => 1024,
            ],
            [
                'id' => 2,
                'title' => 'Professional',
                'price' => 79.00,
                'special_price' => null,
                'description' => 'Ideal for growing businesses',
                'is_active' => true,
                'max_members' => 50,
                'max_storage' => 5120,
            ],
            [
                'id' => 3,
                'title' => 'Enterprise',
                'price' => 199.00,
                'special_price' => null,
                'description' => 'For large organizations',
                'is_active' => true,
                'max_members' => null,
                'max_storage' => null,
            ],
        ];

        foreach ($tiers as $tierData) {
            Tier::firstOrCreate(
                ['id' => $tierData['id']],
                $tierData
            );
        }
    }
}
