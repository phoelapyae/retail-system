<?php

namespace App\Modules\Loyalty\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\ModuleRegistry;
use App\Modules\Loyalty\Services\LoyaltyService;
use App\Modules\Loyalty\Interfaces\LoyaltyServiceInterface;
use App\Modules\Loyalty\Listeners\AwardPointsListener;
use App\Core\Events\{SaleCompleted, OrderCreated};

class LoyaltyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LoyaltyService::class);
        $this->app->bind(LoyaltyServiceInterface::class, LoyaltyService::class);
        
        ModuleRegistry::register('loyalty', LoyaltyService::class);
        
        $this->mergeConfigFrom(__DIR__ . '/../Config/loyalty.php', 'loyalty');
    }

    public function boot()
    {
        // Listen to events from other modules
        $this->app['events']->listen(SaleCompleted::class, AwardPointsListener::class);
        $this->app['events']->listen(OrderCreated::class, AwardPointsListener::class);
        
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'loyalty');
    }
}