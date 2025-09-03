<?php

namespace App\Modules\POS\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\POS\Models\PosSale;
use App\Core\Models\Customer;

class PosSaleFactory extends Factory
{
    protected $model = PosSale::class;
    
    public function definition()
    {
        return [
            'customer_id' => Customer::factory(),
            'total_amount' => $this->faker->randomFloat(2, 10, 500),
            'tax_amount' => function (array $attributes) {
                return $attributes['total_amount'] * 0.08;
            },
            'discount_amount' => 0,
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'mobile']),
            'status' => 'completed',
            'register_id' => 1,
            'cashier_id' => 1,
            'receipt_number' => 'POS-' . $this->faker->unique()->numerify('########')
        ];
    }
}