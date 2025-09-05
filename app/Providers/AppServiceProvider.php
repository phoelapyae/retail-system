<?php

namespace App\Providers;

use App\Core\Middleware\JsonResponseMiddleware;
use App\Core\Middleware\ModuleEnabledMiddleware;
use App\Core\Providers\ModularAppServiceProvider;
use App\Core\Services\ModuleRegistry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register core modular system first
        $this->app->register(ModularAppServiceProvider::class);
        
        // Auto-register available module providers
        $this->registerModuleProviders();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register global middleware
        $this->registerMiddleware();
        
        // Register global view composers
        // $this->registerViewComposers();
    }

    /**
     * Register module providers based on availability
     */
    private function registerModuleProviders()
    {
        $modules = [
            'pos' => 'App\Modules\POS\Providers\POSServiceProvider',
            'ecommerce' => 'App\Modules\Ecommerce\Providers\EcommerceServiceProvider',
            'loyalty' => 'App\Modules\Loyalty\Providers\LoyaltyServiceProvider',
        ];
        
        foreach ($modules as $name => $providerClass) {
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
                Log::info("Registered module provider: {$providerClass}");
            } else {
                Log::debug("Module provider not found: {$providerClass}");
            }
        }
    }

    /**
     * Register global middleware
     */
    private function registerMiddleware()
    {
        // Register module middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('module', ModuleEnabledMiddleware::class);
        $router->aliasMiddleware('json.response', JsonResponseMiddleware::class);
    }
    
    // /**
    //  * Register view composers
    //  */
    // private function registerViewComposers()
    // {
    //     // Global view composers can be registered here
    //     view()->composer('*', function ($view) {
    //         $view->with('availableModules', ModuleRegistry::getAvailableModules());
    //     });
    // }
}