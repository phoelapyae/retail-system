<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Log;

class ModuleRegistry
{
    private static $availableModules = [];
    
    public static function register($moduleName, $serviceClass)
    {
        if (class_exists($serviceClass)) {
            self::$availableModules[$moduleName] = $serviceClass;
            Log::info("Module registered: {$moduleName}");
        }
    }
    
    public static function isAvailable($moduleName): bool
    {
        return isset(self::$availableModules[$moduleName]);
    }
    
    public static function getService($moduleName)
    {
        return self::$availableModules[$moduleName] ?? null;
    }
    
    public static function getAvailableModules(): array
    {
        return array_keys(self::$availableModules);
    }
    
    public static function getAllServices(): array
    {
        return self::$availableModules;
    }
}