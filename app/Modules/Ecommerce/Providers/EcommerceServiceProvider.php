<?php

namespace App\Modules\Ecommerce\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\ModuleRegistry;
use App\Modules\Ecommerce\Services\EcommerceService;
use App\Modules\Ecommerce\Interfaces\EcommerceServiceInterface;
use App\Modules\Ecommerce\Listeners\UpdateInventoryListener;
use App\Core\Events\SaleCompleted;

class EcommerceServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        $this->app->singleton(EcommerceService::class);
        $this->app->bind(EcommerceServiceInterface::class, EcommerceService::class);
        
        // Register in module registry
        ModuleRegistry::register('ecommerce', EcommerceService::class);
        
        // Load module config
        $this->mergeConfigFrom(__DIR__ . '/../Config/ecommerce.php', 'ecommerce');
    }

    public function boot()
    {
        // Register event listeners
        $this->app['events']->listen(SaleCompleted::class, UpdateInventoryListener::class);
        
        // Load module views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'ecommerce');
        
        // Publish module assets
        $this->publishes([
            __DIR__ . '/../Config/ecommerce.php' => config_path('ecommerce.php'),
        ], 'ecommerce-config');
    }
}