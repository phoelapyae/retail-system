<?php

namespace App\Modules\Loyalty\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Loyalty\Models\LoyaltyTier;

class LoyaltySeeder extends Seeder
{
    public function run()
    {
        $tiers = [
            [
                'name' => 'Bronze',
                'min_points' => 0,
                'max_points' => 499,
                'benefits' => ['Basic member benefits'],
                'multiplier' => 1.00
            ],
            [
                'name' => 'Silver',
                'min_points' => 500,
                'max_points' => 999,
                'benefits' => ['5% discount on purchases', 'Birthday bonus points'],
                'multiplier' => 1.25
            ],
            [
                'name' => 'Gold',
                'min_points' => 1000,
                'max_points' => null,
                'benefits' => ['10% discount on purchases', 'Free shipping', 'Priority customer service'],
                'multiplier' => 1.50
            ]
        ];
        
        foreach ($tiers as $tier) {
            LoyaltyTier::create($tier);
        }
    }
}