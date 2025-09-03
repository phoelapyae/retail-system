<?php

namespace App\Modules\Ecommerce\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Ecommerce\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;
    
    public function definition()
    {
        $price = $this->faker->randomFloat(2, 5, 500);
        $cost = $price * 0.6; // 40% margin
        
        return [
            'sku' => strtoupper($this->faker->unique()->bothify('??###??')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $price,
            'cost' => $cost,
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'low_stock_threshold' => $this->faker->numberBetween(5, 15),
            'category' => $this->faker->randomElement(['Electronics', 'Clothing', 'Home', 'Books', 'Sports']),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'dimensions' => [
                'length' => $this->faker->randomFloat(2, 1, 50),
                'width' => $this->faker->randomFloat(2, 1, 50),
                'height' => $this->faker->randomFloat(2, 1, 50)
            ]
        ];
    }
}