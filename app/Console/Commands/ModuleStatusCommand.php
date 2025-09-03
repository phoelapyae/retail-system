<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Services\{ModuleRegistry, ModuleCommunicationHub, ModuleHealthChecker};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ModuleStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:status 
                            {action? : The action to perform (list, health, metrics, enable, disable, reset)}
                            {module? : The module name (for enable/disable actions)}
                            {--format=table : Output format (table, json, csv)}
                            {--save : Save health check results to cache}
                            {--detailed : Show detailed information}
                            {--refresh : Refresh cached data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage and monitor module status, health, and metrics';

    private $hub;
    private $healthChecker;

    public function __construct(ModuleCommunicationHub $hub, ModuleHealthChecker $healthChecker)
    {
        parent::__construct();
        $this->hub = $hub;
        $this->healthChecker = $healthChecker;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action') ?? 'list';
        $format = $this->option('format');

        try {
            switch ($action) {
                case 'list':
                    return $this->listModules($format);
                    
                case 'health':
                    return $this->checkHealth($format);
                    
                case 'metrics':
                    return $this->showMetrics($format);
                    
                case 'enable':
                    return $this->enableModule();
                    
                case 'disable':
                    return $this->disableModule();
                    
                case 'reset':
                    return $this->resetModuleCache();
                    
                case 'monitor':
                    return $this->continuousMonitoring();
                    
                case 'export':
                    return $this->exportModuleData();
                    
                default:
                    $this->error("Unknown action: {$action}");
                    $this->info("Available actions: list, health, metrics, enable, disable, reset, monitor, export");
                    return 1;
            }
        } catch (\Exception $e) {
            $this->error("Command failed: " . $e->getMessage());
            Log::error("Module status command failed", [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * List all modules with their status
     */
    private function listModules($format = 'table')
    {
        $this->info('📋 Module Status Overview');
        $this->line('');

        $modules = config('modules', []);
        $availableModules = ModuleRegistry::getAvailableModules();
        $detailed = $this->option('detailed');

        if (empty($modules)) {
            $this->warn('No modules configured in config/modules.php');
            return 0;
        }

        $data = [];
        foreach ($modules as $name => $config) {
            $isAvailable = in_array($name, $availableModules);
            $providerExists = class_exists($config['service_provider'] ?? '');
            
            $row = [
                'Module' => ucfirst($name),
                'Config Status' => $config['enabled'] ? '✅ Enabled' : '❌ Disabled',
                'Runtime Status' => $isAvailable ? '🟢 Available' : '🔴 Missing',
                'Provider' => $providerExists ? '✅ Found' : '❌ Missing',
            ];

            if ($detailed) {
                $migrationPath = base_path($config['migration_path'] ?? '');
                $routesPath = base_path($config['routes'] ?? '');
                
                $row['Migrations'] = is_dir($migrationPath) ? '✅ Found' : '❌ Missing';
                $row['Routes'] = file_exists($routesPath) ? '✅ Found' : '❌ Missing';
                $row['Namespace'] = $config['namespace'] ?? 'N/A';
            }

            $data[] = $row;
        }

        $this->outputData($data, $format);

        // Summary
        $totalModules = count($modules);
        $enabledModules = count(array_filter($modules, fn($config) => $config['enabled']));
        $availableCount = count($availableModules);

        $this->line('');
        $this->info("📊 Summary:");
        $this->line("   Total Modules: {$totalModules}");
        $this->line("   Enabled: {$enabledModules}");
        $this->line("   Runtime Available: {$availableCount}");

        if ($enabledModules !== $availableCount) {
            $this->warn("   ⚠️  Some enabled modules are not available at runtime!");
        }

        return 0;
    }

    /**
     * Check module health
     */
    private function checkHealth($format = 'table')
    {
        $this->info('🏥 Module Health Check');
        $this->line('');

        if ($this->option('refresh')) {
            Cache::forget('module_health_status');
            $this->info('♻️  Refreshed cached health data');
            $this->line('');
        }

        $healthData = $this->option('save') && !$this->option('refresh') 
            ? Cache::remember('module_health_status', 300, fn() => $this->healthChecker->checkAllModules())
            : $this->healthChecker->checkAllModules();

        if (empty($healthData)) {
            $this->warn('No modules available for health check');
            return 0;
        }

        $data = [];
        $healthyCount = 0;
        $unhealthyModules = [];

        foreach ($healthData as $module => $status) {
            $healthy = $status['healthy'] ?? false;
            $tablesOk = $status['tables_exist'] ?? false;
            
            if ($healthy) {
                $healthyCount++;
            } else {
                $unhealthyModules[] = $module;
            }

            $row = [
                'Module' => ucfirst($module),
                'Health' => $healthy ? '🟢 Healthy' : '🔴 Unhealthy',
                'Tables' => $tablesOk ? '✅ OK' : '❌ Missing',
                'Service' => $status['available'] ? '✅ Available' : '❌ Unavailable',
                'Last Check' => isset($status['last_checked']) 
                    ? Carbon::parse($status['last_checked'])->diffForHumans() 
                    : 'Just now'
            ];

            if ($this->option('detailed') && isset($status['error'])) {
                $row['Error'] = substr($status['error'], 0, 50) . '...';
            }

            $data[] = $row;
        }

        $this->outputData($data, $format);

        // Health Summary
        $totalModules = count($healthData);
        $this->line('');
        $this->info("🏥 Health Summary:");
        $this->line("   Total Modules Checked: {$totalModules}");
        $this->line("   Healthy: {$healthyCount}");
        $this->line("   Unhealthy: " . count($unhealthyModules));

        if (!empty($unhealthyModules)) {
            $this->warn("   ⚠️  Unhealthy modules: " . implode(', ', $unhealthyModules));
            $this->line('');
            $this->info("💡 Troubleshooting tips:");
            $this->line("   - Check database connections");
            $this->line("   - Verify migration files exist");
            $this->line("   - Ensure module tables are created");
            $this->line("   - Check log files for detailed errors");
        }

        if ($this->option('save')) {
            Cache::put('module_health_status', $healthData, 300);
            $this->info('💾 Health status saved to cache');
        }

        return count($unhealthyModules) > 0 ? 1 : 0;
    }

    /**
     * Show module metrics and statistics
     */
    private function showMetrics($format = 'table')
    {
        $this->info('📊 Module Metrics');
        $this->line('');

        try {
            $metrics = $this->healthChecker->getModuleMetrics();
            
            if (empty($metrics)) {
                $this->warn('No metrics available');
                return 0;
            }

            $data = [];
            
            foreach ($metrics as $module => $moduleMetrics) {
                foreach ($moduleMetrics as $metric => $value) {
                    $data[] = [
                        'Module' => ucfirst($module),
                        'Metric' => $this->formatMetricName($metric),
                        'Value' => $this->formatMetricValue($metric, $value),
                        'Updated' => now()->format('H:i:s')
                    ];
                }
            }

            if (!empty($data)) {
                $this->outputData($data, $format);
                
                // Additional insights
                $this->line('');
                $this->info("📈 Insights:");
                $this->showMetricInsights($metrics);
            } else {
                $this->info('No detailed metrics available for current modules');
            }

        } catch (\Exception $e) {
            $this->error("Failed to retrieve metrics: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Enable a module
     */
    private function enableModule()
    {
        $module = $this->argument('module');
        
        if (!$module) {
            $module = $this->choice('Which module would you like to enable?', 
                array_keys(config('modules', [])));
        }

        if (!$module) {
            $this->error('No module specified');
            return 1;
        }

        $modules = config('modules', []);
        
        if (!isset($modules[strtolower($module)])) {
            $this->error("Module '{$module}' not found in configuration");
            return 1;
        }

        $moduleKey = strtolower($module);
        $envKey = 'MODULE_' . strtoupper($module) . '_ENABLED';
        
        // Update .env file
        $this->updateEnvFile($envKey, 'true');
        
        $this->info("✅ Module '{$module}' has been enabled");
        $this->info("🔄 You may need to restart your application for changes to take effect");
        
        // Clear caches
        $this->call('config:clear');
        Cache::forget('module_health_status');
        
        return 0;
    }

    /**
     * Disable a module
     */
    private function disableModule()
    {
        $module = $this->argument('module');
        
        if (!$module) {
            $availableModules = array_keys(array_filter(
                config('modules', []), 
                fn($config) => $config['enabled']
            ));
            
            if (empty($availableModules)) {
                $this->warn('No modules are currently enabled');
                return 0;
            }
            
            $module = $this->choice('Which module would you like to disable?', $availableModules);
        }

        if (!$module) {
            $this->error('No module specified');
            return 1;
        }

        if (!$this->confirm("Are you sure you want to disable the '{$module}' module?")) {
            $this->info('Operation cancelled');
            return 0;
        }

        $envKey = 'MODULE_' . strtoupper($module) . '_ENABLED';
        
        // Update .env file
        $this->updateEnvFile($envKey, 'false');
        
        $this->info("❌ Module '{$module}' has been disabled");
        $this->info("🔄 You may need to restart your application for changes to take effect");
        
        // Clear caches
        $this->call('config:clear');
        Cache::forget('module_health_status');
        
        return 0;
    }

    /**
     * Reset module cache and refresh status
     */
    private function resetModuleCache()
    {
        $this->info('🔄 Resetting module cache...');
        
        // Clear various caches
        Cache::forget('module_health_status');
        $this->call('config:clear');
        $this->call('route:clear');
        
        // Re-register modules
        app()->register(\App\Core\Providers\ModularAppServiceProvider::class);
        
        $this->info('✅ Module cache has been reset');
        $this->info('📋 Current module status:');
        
        return $this->listModules();
    }

    /**
     * Continuous monitoring mode
     */
    private function continuousMonitoring()
    {
        $this->info('🔍 Starting continuous module monitoring (Press Ctrl+C to stop)');
        $this->line('');

        $interval = 30; // seconds
        $iteration = 0;

        while (true) {
            $iteration++;
            
            $this->line("🔄 Check #{$iteration} - " . now()->format('Y-m-d H:i:s'));
            
            $healthData = $this->healthChecker->checkAllModules();
            $unhealthyModules = array_filter($healthData, fn($status) => !$status['healthy']);
            
            if (empty($unhealthyModules)) {
                $this->info("✅ All modules healthy (" . count($healthData) . " checked)");
            } else {
                $this->error("❌ " . count($unhealthyModules) . " unhealthy modules detected:");
                foreach ($unhealthyModules as $module => $status) {
                    $this->line("   - {$module}: " . ($status['error'] ?? 'Unknown error'));
                }
            }
            
            $this->line('');
            sleep($interval);
        }
    }

    /**
     * Export module data to file
     */
    private function exportModuleData()
    {
        $this->info('📤 Exporting module data...');
        
        $exportData = [
            'timestamp' => now()->toISOString(),
            'modules' => config('modules', []),
            'health_status' => $this->healthChecker->checkAllModules(),
            'metrics' => $this->healthChecker->getModuleMetrics(),
            'available_modules' => ModuleRegistry::getAvailableModules(),
        ];
        
        $filename = 'module-status-' . now()->format('Y-m-d-H-i-s') . '.json';
        $filepath = storage_path("logs/{$filename}");
        
        file_put_contents($filepath, json_encode($exportData, JSON_PRETTY_PRINT));
        
        $this->info("✅ Module data exported to: {$filepath}");
        $this->line("📊 Export includes:");
        $this->line("   - Module configurations");
        $this->line("   - Health status");
        $this->line("   - Performance metrics");
        $this->line("   - Runtime availability");
        
        return 0;
    }

    /**
     * Output data in specified format
     */
    private function outputData(array $data, string $format)
    {
        if (empty($data)) {
            $this->info('No data to display');
            return;
        }

        switch ($format) {
            case 'json':
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
                break;
                
            case 'csv':
                if (!empty($data)) {
                    // Output CSV headers
                    $this->line(implode(',', array_keys($data[0])));
                    
                    // Output CSV data
                    foreach ($data as $row) {
                        $this->line(implode(',', array_map(function($value) {
                            return '"' . str_replace('"', '""', strip_tags($value)) . '"';
                        }, $row)));
                    }
                }
                break;
                
            default: // table
                if (!empty($data)) {
                    $headers = array_keys($data[0]);
                    $this->table($headers, $data);
                }
                break;
        }
    }

    /**
     * Format metric name for display
     */
    private function formatMetricName(string $metric): string
    {
        return ucwords(str_replace('_', ' ', $metric));
    }

    /**
     * Format metric value for display
     */
    private function formatMetricValue(string $metric, $value): string
    {
        if (is_numeric($value)) {
            if (str_contains($metric, 'revenue') || str_contains($metric, 'amount')) {
                return '$' . number_format($value, 2);
            }
            return number_format($value);
        }
        
        return (string) $value;
    }

    /**
     * Show metric insights
     */
    private function showMetricInsights(array $metrics)
    {
        foreach ($metrics as $module => $moduleMetrics) {
            $insights = $this->generateModuleInsights($module, $moduleMetrics);
            if (!empty($insights)) {
                $this->line("   📈 {$module}: " . implode(', ', $insights));
            }
        }
    }

    /**
     * Generate insights for a module
     */
    private function generateModuleInsights(string $module, array $metrics): array
    {
        $insights = [];
        
        switch ($module) {
            case 'pos':
                if (isset($metrics['today_sales']) && $metrics['today_sales'] > 0) {
                    $insights[] = "{$metrics['today_sales']} sales today";
                }
                if (isset($metrics['total_revenue'])) {
                    $insights[] = "\${$metrics['total_revenue']} total revenue";
                }
                break;
                
            case 'ecommerce':
                if (isset($metrics['low_stock_products']) && $metrics['low_stock_products'] > 0) {
                    $insights[] = "{$metrics['low_stock_products']} products need restocking";
                }
                if (isset($metrics['total_products'])) {
                    $insights[] = "{$metrics['total_products']} products in catalog";
                }
                break;
                
            case 'loyalty':
                if (isset($metrics['total_customers_with_points'])) {
                    $insights[] = "{$metrics['total_customers_with_points']} active loyalty members";
                }
                break;
        }
        
        return $insights;
    }

    /**
     * Update environment file
     */
    private function updateEnvFile(string $key, string $value)
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            $this->warn('.env file not found, creating from .env.example');
            copy(base_path('.env.example'), $envFile);
        }
        
        $env = file_get_contents($envFile);
        
        // Check if key exists
        if (preg_match("/^{$key}=/m", $env)) {
            // Update existing key
            $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
        } else {
            // Add new key
            $env .= "\n{$key}={$value}\n";
        }
        
        file_put_contents($envFile, $env);
    }

    /**
     * Get command usage statistics
     */
    private function getUsageStats(): array
    {
        $logFile = storage_path('logs/module-commands.log');
        
        if (!file_exists($logFile)) {
            return ['total_runs' => 0, 'last_run' => null];
        }
        
        $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        return [
            'total_runs' => count($logs),
            'last_run' => end($logs) ?: null
        ];
    }

    /**
     * Log command usage
     */
    private function logUsage()
    {
        $logFile = storage_path('logs/module-commands.log');
        $logEntry = now()->toISOString() . ' - ' . $this->signature . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}