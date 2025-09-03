<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleInstallCommand extends Command
{
    protected $signature = 'module:install {name}';
    protected $description = 'Install a new module';

    public function handle()
    {
        $name = ucfirst($this->argument('name'));
        $modulePath = app_path("Modules/{$name}");
        
        if (File::exists($modulePath)) {
            $this->error("Module {$name} already exists!");
            return 1;
        }
        
        $this->info("Installing module: {$name}");
        
        // Create directory structure
        $this->createModuleStructure($name, $modulePath);
        
        // Create basic files
        $this->createModuleFiles($name, $modulePath);
        
        // Add to modules config
        $this->addToModulesConfig($name);
        
        $this->info("Module {$name} installed successfully!");
        $this->info("Don't forget to:");
        $this->info("1. Register the service provider");
        $this->info("2. Create migrations");
        $this->info("3. Add routes");
        
        return 0;
    }
    
    private function createModuleStructure($name, $path)
    {
        $directories = [
            'Controllers',
            'Models',
            'Services',
            'Requests',
            'Resources',
            'Interfaces',
            'Providers',
            'Database/Migrations',
            'Database/Seeders',
            'Database/Factories',
            'Tests/Unit',
            'Tests/Feature',
            'Config'
        ];
        
        foreach ($directories as $dir) {
            File::makeDirectory("{$path}/{$dir}", 0755, true);
        }
    }
    
    private function createModuleFiles($name, $path)
    {
        // Create basic service
        $serviceContent = $this->getServiceStub($name);
        File::put("{$path}/Services/{$name}Service.php", $serviceContent);
        
        // Create service provider
        $providerContent = $this->getServiceProviderStub($name);
        File::put("{$path}/Providers/{$name}ServiceProvider.php", $providerContent);
        
        // Create interface
        $interfaceContent = $this->getInterfaceStub($name);
        File::put("{$path}/Interfaces/{$name}ServiceInterface.php", $interfaceContent);
    }
    
    private function addToModulesConfig($name)
    {
        $configPath = config_path('modules.php');
        
        if (!File::exists($configPath)) {
            File::put($configPath, "<?php\n\nreturn [\n\n];");
        }
        
        $config = include $configPath;
        $config[strtolower($name)] = [
            'enabled' => env("MODULE_" . strtoupper($name) . "_ENABLED", false),
            'namespace' => "App\\Modules\\{$name}",
            'service_provider' => "App\\Modules\\{$name}\\Providers\\{$name}ServiceProvider",
            'migration_path' => "app/Modules/{$name}/Database/Migrations",
            'routes' => "routes/modules/" . strtolower($name) . ".php",
        ];
        
        $configContent = "<?php\n\nreturn " . var_export($config, true) . ";";
        File::put($configPath, $configContent);
    }
    
    private function getServiceStub($name)
    {
        return "<?php

namespace App\\Modules\\{$name}\\Services;

use App\\Core\\Interfaces\\ModuleServiceInterface;
use App\\Core\\Events\\ModuleEvent;
use App\\Core\\Services\\ModuleCommunicationHub;

class {$name}Service implements ModuleServiceInterface
{
    private \$hub;
    
    public function __construct(ModuleCommunicationHub \$hub)
    {
        \$this->hub = \$hub;
    }
    
    public function handleEvent(ModuleEvent \$event)
    {
        // Handle events from other modules
    }
    
    public function isHealthy(): bool
    {
        return true;
    }
    
    public function getModuleName(): string
    {
        return '" . strtolower($name) . "';
    }
}
";
    }
    
    private function getServiceProviderStub($name)
    {
        return "<?php

namespace App\\Modules\\{$name}\\Providers;

use Illuminate\\Support\\ServiceProvider;
use App\\Core\\Services\\ModuleRegistry;
use App\\Modules\\{$name}\\Services\\{$name}Service;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register()
    {
        \$this->app->singleton({$name}Service::class);
        ModuleRegistry::register('" . strtolower($name) . "', {$name}Service::class);
    }
    
    public function boot()
    {
        \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
";
    }
    
    private function getInterfaceStub($name)
    {
        return "<?php

namespace App\\Modules\\{$name}\\Interfaces;

interface {$name}ServiceInterface
{
    // Define module-specific interface methods
}
";
    }
}