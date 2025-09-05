<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\ModuleRegistry;
use App\Core\Services\ModuleCommunicationHub;
use App\Core\Services\SafeEventDispatcher;
use App\Core\Services\NullModuleService;
use App\Core\Services\AutoModuleScanner;

class ModularAppServiceProvider extends ServiceProvider
{
    protected $scanner;

    public function register()
    {
        // Register scanner first
        $this->app->singleton(AutoModuleScanner::class);
        $this->scanner = $this->app->make(AutoModuleScanner::class);

        // Register core services
        $this->app->singleton(ModuleCommunicationHub::class);
        $this->app->singleton(SafeEventDispatcher::class);

        // Auto-register available modules
        $this->registerAvailableModules();
    }

    public function boot()
    {
        // Register module migrations automatically
        $this->loadModuleMigrations();

        // Register event listeners for available modules
        $this->registerEventListeners();
    }

    /**
     * Automatically load migrations from all modules
     */
    private function loadModuleMigrations()
    {
        $migrationPaths = $this->scanner->scanMigrationPaths();

        foreach ($migrationPaths as $path) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * Automatically register all available modules
     */
    private function registerAvailableModules()
    {
        $modules = $this->scanner->scanModules();

        foreach ($modules as $name => $serviceClass) {
            ModuleRegistry::register($name, $serviceClass);
            $this->app->singleton($serviceClass);

            // Create interface binding automatically
            $interface = str_replace('Service', 'ServiceInterface', $serviceClass);
            if (interface_exists($interface)) {
                $this->app->bind($interface, $serviceClass);
            }
        }

        // Bind null services for any missing interfaces
        $this->bindNullServices($modules);
    }

    /**
     * Bind null services for missing modules
     */
    private function bindNullServices($modules)
    {
        foreach ($modules as $name => $serviceClass) {
            $interface = str_replace('Service', 'ServiceInterface', $serviceClass);

            if (interface_exists($interface) && !$this->app->bound($interface)) {
                $this->app->bind($interface, NullModuleService::class);
            }
        }
    }

    /**
     * Automatically register event listeners
     */
    private function registerEventListeners()
    {
        $events = $this->scanner->scanEvents();

        foreach ($events as $eventClass) {
            $this->app['events']->listen($eventClass, function ($event) {
                foreach (ModuleRegistry::getAvailableModules() as $moduleName) {
                    if (ModuleRegistry::isAvailable($moduleName)) {
                        $service = app(ModuleRegistry::getService($moduleName));

                        // Check if service has handleEvent method
                        if (method_exists($service, 'handleEvent')) {
                            $service->handleEvent($event);
                        }
                    }
                }
            });
        }
    }
}