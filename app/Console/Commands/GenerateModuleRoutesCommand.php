<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateModuleRoutesCommand extends Command
{
    protected $signature = 'module:routes {module} {--type=api : Route type (api or web)}';
    protected $description = 'Generate route files for a module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $type = $this->option('type');
        $modulePath = app_path("Modules/{$module}");
        
        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist!");
            return 1;
        }
        
        $routesPath = "{$modulePath}/Routes";
        
        if (!File::exists($routesPath)) {
            File::makeDirectory($routesPath, 0755, true);
        }
        
        $routeFile = "{$routesPath}/{$type}.php";
        
        if (File::exists($routeFile)) {
            if (!$this->confirm("Route file {$type}.php already exists. Overwrite?")) {
                return 0;
            }
        }
        
        $content = $this->getRouteStub($module, $type);
        File::put($routeFile, $content);
        
        $this->info("Generated {$type} routes for {$module} module");
        
        return 0;
    }
    
    private function getRouteStub($module, $type)
    {
        $namespace = "App\\Modules\\{$module}\\Controllers";
        $prefix = strtolower($module);
        
        if ($type === 'api') {
            return "<?php

use Illuminate\\Support\\Facades\\Route;
use {$namespace}\\{$module}Controller;

Route::group([
    'prefix' => '{$prefix}',
    'middleware' => ['api', 'module:{$prefix}']
], function () {
    Route::get('/', [{$module}Controller::class, 'index']);
    Route::post('/', [{$module}Controller::class, 'store']);
    Route::get('/{id}', [{$module}Controller::class, 'show']);
    Route::put('/{id}', [{$module}Controller::class, 'update']);
    Route::delete('/{id}', [{$module}Controller::class, 'destroy']);
});
";
        } else {
            return "<?php

use Illuminate\\Support\\Facades\\Route;
use {$namespace}\\Web\\{$module}WebController;

Route::group([
    'prefix' => '{$prefix}',
    'middleware' => ['web', 'auth', 'module:{$prefix}'],
    'namespace' => '{$namespace}\\Web'
], function () {
    Route::get('/', [{$module}WebController::class, 'index'])->name('{$prefix}.index');
    Route::get('/dashboard', [{$module}WebController::class, 'dashboard'])->name('{$prefix}.dashboard');
});
";
        }
    }
}