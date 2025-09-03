<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Core\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        Customer::factory(50)->create();
    }
}