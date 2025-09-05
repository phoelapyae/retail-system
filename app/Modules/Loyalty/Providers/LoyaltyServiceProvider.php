<?php

namespace App\Modules\Loyalty\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\ModuleRegistry;
use App\Modules\Loyalty\Listeners\AwardPointsListener;
use App\Core\Events\{SaleCompleted, OrderCreated};
use App\Modules\Loyalty\Services\TierService;
use Illuminate\Support\Facades\Route;

class LoyaltyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TierService::class);
        
        ModuleRegistry::register('loyalty', TierService::class);
        
        $this->mergeConfigFrom(__DIR__ . '/../Config/loyalty.php', 'loyalty');
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
        
        // Register scheduled tasks
        $this->registerScheduledTasks();
        
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
        $this->app['events']->listen(SaleCompleted::class, AwardPointsListener::class);
        $this->app['events']->listen(OrderCreated::class, AwardPointsListener::class);
    }

    /**
     * Register console commands
     */
    protected function registerCommands()
    {
        $this->commands([
            \App\Modules\Loyalty\Console\Commands\ExpirePointsCommand::class,
        ]);
    }

    /**
     * Register scheduled tasks
     */
    protected function registerScheduledTasks()
    {
        // This will be called by the main scheduler
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            // Run point expiry daily at midnight
            $schedule->command('loyalty:expire-points')
                    ->daily()
                    ->at('00:00');
                    
            // Update customer tiers daily
            $schedule->command('loyalty:update-tiers')
                    ->daily()
                    ->at('01:00');
                    
            // Send expiry notifications weekly
            $schedule->command('loyalty:send-expiry-notifications')
                    ->weekly()
                    ->sundays()
                    ->at('09:00');
        });
    }

    /**
     * Register publishable assets
     */
    protected function registerPublishables()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/loyalty.php' => config_path('loyalty.php'),
        ], 'loyalty-config');
    }
}