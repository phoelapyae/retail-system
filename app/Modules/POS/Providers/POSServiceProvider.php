<?php

namespace App\Modules\POS\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\ModuleRegistry;
use App\Modules\POS\Services\POSService;
use Illuminate\Support\Facades\Route;

class POSServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(POSService::class);
        ModuleRegistry::register('pos', POSService::class);

        // Load module configuration
        $this->mergeConfigFrom(__DIR__ . '/../Config/pos.php', 'pos');
    }
    
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->registerRoutes();
        
        // Register console commands if running in console
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
     * Register console commands
     */
    protected function registerCommands()
    {
        $this->commands([
            // Add POS-specific commands here
        ]);
    }

    /**
     * Register publishable assets
     */
    protected function registerPublishables()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/pos.php' => config_path('pos.php'),
        ], 'pos-config');

         // Publish migrations
        $this->publishes([
            __DIR__ . '/../Database/Migrations/' => database_path('migrations'),
        ], 'pos-migrations');
    }
}