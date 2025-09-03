<?php

namespace App\Modules\POS\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\POS\Models\Register;

class POSSeeder extends Seeder
{
    public function run()
    {
        // Create default registers
        Register::create([
            'name' => 'Main Register',
            'location' => 'Front Counter',
            'status' => 'closed'
        ]);
        
        Register::create([
            'name' => 'Express Register',
            'location' => 'Express Lane',
            'status' => 'closed'
        ]);
    }
}