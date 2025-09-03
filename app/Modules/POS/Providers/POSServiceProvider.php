<?php

namespace App\Modules\POS\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\ModuleRegistry;
use App\Modules\POS\Services\POSService;

class POSServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(POSService::class);
        ModuleRegistry::register('pos', POSService::class);
    }
    
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}