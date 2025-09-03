<?php

namespace App\Modules\Ecommerce\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Ecommerce\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'sku' => 'ITEM001',
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'price' => 99.99,
                'cost' => 45.00,
                'stock_quantity' => 50,
                'low_stock_threshold' => 10,
                'category' => 'Electronics',
                'status' => 'active'
            ],
            [
                'sku' => 'ITEM002',
                'name' => 'Smart Water Bottle',
                'description' => 'Temperature tracking smart water bottle',
                'price' => 34.99,
                'cost' => 15.00,
                'stock_quantity' => 25,
                'low_stock_threshold' => 5,
                'category' => 'Health',
                'status' => 'active'
            ],
            [
                'sku' => 'ITEM003',
                'name' => 'Ergonomic Office Chair',
                'description' => 'Comfortable office chair with lumbar support',
                'price' => 249.99,
                'cost' => 120.00,
                'stock_quantity' => 15,
                'low_stock_threshold' => 3,
                'category' => 'Furniture',
                'status' => 'active'
            ],
            [
                'sku' => 'ITEM004',
                'name' => 'Organic Coffee Beans',
                'description' => 'Premium organic coffee beans from Colombia',
                'price' => 19.99,
                'cost' => 8.00,
                'stock_quantity' => 100,
                'low_stock_threshold' => 20,
                'category' => 'Food & Beverage',
                'status' => 'active'
            ]
        ];
        
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}