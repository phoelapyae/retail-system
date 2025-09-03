<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Always seed core data
        $this->call([
            UserSeeder::class,
            CustomerSeeder::class,
        ]);
        
        // Conditionally seed module data
        $this->seedModuleData();
    }
    
    private function seedModuleData()
    {
        $modules = config('modules', []);
        
        foreach ($modules as $name => $config) {
            if ($config['enabled']) {
                $seederClass = $config['namespace'] . '\\Database\\Seeders\\' . ucfirst($name) . 'Seeder';
                
                if (class_exists($seederClass)) {
                    $this->call($seederClass);
                }
            }
        }
    }
}