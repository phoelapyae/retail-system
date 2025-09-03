<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModuleCommand extends Command
{
    protected $signature = 'module {action} {name?}';
    protected $description = 'Manage application modules';

    public function handle()
    {
        $action = $this->argument('action');
        $name = $this->argument('name');

        switch ($action) {
            case 'list':
                $this->listModules();
                break;
            case 'status':
                $this->showModuleStatus($name);
                break;
            case 'health':
                $this->checkModuleHealth();
                break;
        }
    }

    private function listModules()
    {
        $modules = config('modules', []);
        
        $this->table(['Module', 'Status', 'Provider Exists', 'Migrations'], 
            collect($modules)->map(function ($config, $name) {
                return [
                    $name,
                    $config['enabled'] ? 'Enabled' : 'Disabled',
                    class_exists($config['service_provider']) ? 'Yes' : 'No',
                    is_dir(base_path($config['migration_path'])) ? 'Yes' : 'No',
                ];
            })->toArray()
        );
    }
}