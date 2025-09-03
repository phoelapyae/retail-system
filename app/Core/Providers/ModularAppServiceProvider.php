<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\{ModuleRegistry, SafeEventDispatcher, ModuleCommunicationHub};

class ModularAppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(SafeEventDispatcher::class);
        $this->app->singleton(ModuleCommunicationHub::class);
        
        $this->registerAvailableModules();
    }
    
    private function registerAvailableModules()
    {
        $modules = [
            'pos' => 'App\Modules\POS\Services\POSService',
            'ecommerce' => 'App\Modules\Ecommerce\Services\EcommerceService',
            'loyalty' => 'App\Modules\Loyalty\Services\LoyaltyService',
        ];
        
        foreach ($modules as $name => $serviceClass) {
            if (class_exists($serviceClass)) {
                ModuleRegistry::register($name, $serviceClass);
                $this->app->singleton($serviceClass);
            }
        }
    }
}