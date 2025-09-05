<?php

namespace App\Modules\Ecommerce\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\ModuleRegistry;
use App\Modules\Ecommerce\Services\EcommerceService;
use App\Modules\Ecommerce\Interfaces\EcommerceServiceInterface;
use Illuminate\Support\Facades\Route;

class EcommerceServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        $this->app->singleton(EcommerceService::class);
        
        // Register in module registry
        ModuleRegistry::register('ecommerce', EcommerceService::class);
        
        // Load module config
        $this->mergeConfigFrom(__DIR__ . '/../Config/ecommerce.php', 'ecommerce');
    }

    public function boot()
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        // Register routes
        $this->registerRoutes();
        
        // Register event listeners
        $this->registerEventListeners();
        
        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
        
        // Publish assets
        $this->registerPublishables();
    }

    /**
     * Register module routes
     */
    protected function registerRoutes()
    {
        // API Routes
        Route::middleware(['api'])
            ->prefix('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
    
    /**
     * Register event listeners
     */
    protected function registerEventListeners()
    {
        // Listen to events from other modules
        $this->app['events']->listen(
            'App\Core\Events\SaleCompleted',
            'App\Modules\Ecommerce\Listeners\UpdateInventoryFromPOSListener'
        );
    }
    
    /**
     * Register console commands
     */
    protected function registerCommands()
    {
        $this->commands([
            // \App\Modules\Ecommerce\Console\Commands\SyncInventoryCommand::class,
            // \App\Modules\Ecommerce\Console\Commands\UpdateProductPricesCommand::class,
            // \App\Modules\Ecommerce\Console\Commands\CleanupCartsCommand::class,
        ]);
    }
    
    /**
     * Register publishable assets
     */
    protected function registerPublishables()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/ecommerce.php' => config_path('ecommerce.php'),
        ], 'ecommerce-config');
        
        // Publish migrations
        $this->publishes([
            __DIR__ . '/../Database/Migrations/' => database_path('migrations'),
        ], 'ecommerce-migrations');
    }
}