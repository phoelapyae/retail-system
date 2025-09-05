<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;
use ReflectionClass;

class AutoModuleScanner
{
    protected $modulePath;
    protected $coreEventsPath;

    public function __construct()
    {
        $this->modulePath = app_path('Modules');
        $this->coreEventsPath = app_path('Core/Events');
    }

    /**
     * Scan all available modules
     */
    public function scanModules(): array
    {
        $modules = [];

        if (!File::exists($this->modulePath)) {
            return $modules;
        }

        $directories = File::directories($this->modulePath);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $serviceClass = $this->findServiceClass($moduleName);

            if ($serviceClass && class_exists($serviceClass)) {
                $modules[strtolower($moduleName)] = $serviceClass;
            }
        }

        return $modules;
    }

    /**
     * Find service class for a module
     */
    protected function findServiceClass(string $moduleName): ?string
    {
        $possibleServiceClasses = [
            "App\\Modules\\{$moduleName}\\Services\\{$moduleName}Service",
        ];

        foreach ($possibleServiceClasses as $serviceClass) {
            if (class_exists($serviceClass)) {
                return $serviceClass;
            }
        }

        return null;
    }

    /**
     * Scan all migration paths
     */
    public function scanMigrationPaths(): array
    {
        $paths = [];

        if (!File::exists($this->modulePath)) {
            return $paths;
        }

        $directories = File::directories($this->modulePath);

        foreach ($directories as $directory) {
            $migrationPath = "{$directory}/Database/Migrations";

            if (File::exists($migrationPath)) {
                $paths[] = $migrationPath;
            }
        }

        return $paths;
    }

    /**
     * Scan all available events
     */
    public function scanEvents(): array
    {
        $events = [];

        if (File::exists($this->coreEventsPath)) {
            $events = array_merge($events, $this->scanEventsInDirectory($this->coreEventsPath, 'App\\Core\\Events'));
        }

        // Scan module events
        if (File::exists($this->modulePath)) {
            $directories = File::directories($this->modulePath);

            foreach ($directories as $directory) {
                $moduleName = basename($directory);
                $eventsPath = "{$directory}/Events";

                if (File::exists($eventsPath)) {
                    $events = array_merge(
                        $events,
                        $this->scanEventsInDirectory($eventsPath, "App\\Modules\\{$moduleName}\\Events")
                    );
                }
            }
        }

        return $events;
    }

    /**
     * Scan events in a specific directory
     */
    protected function scanEventsInDirectory(string $path, string $namespace): array
    {
        $events = [];
        $files = File::glob("{$path}/*.php");

        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            $fullClassName = "{$namespace}\\{$className}";

            if (class_exists($fullClassName)) {
                try {
                    $reflection = new ReflectionClass($fullClassName);

                    if ($this->isEventClass($reflection)) {
                        $events[] = $fullClassName;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $events;
    }

    /**
     * Check if a class is an event class
     */
    protected function isEventClass(ReflectionClass $reflection): bool
    {
        // Check if extends ModuleEvent
        $parentClass = $reflection->getParentClass();
        if ($parentClass && $parentClass->getName() === 'App\\Core\\Events\\ModuleEvent') {
            return true;
        }

        // Check if class name ends with common event suffixes
        $className = $reflection->getShortName();
        $eventSuffixes = ['Event', 'Completed', 'Created', 'Updated', 'Deleted', 'Registered'];

        foreach ($eventSuffixes as $suffix) {
            if (str_ends_with($className, $suffix)) {
                return true;
            }
        }

        return false;
    }
}