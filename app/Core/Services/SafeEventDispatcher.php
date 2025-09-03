<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use App\Core\Events\ModuleEvent;

class SafeEventDispatcher
{
    public function dispatch($event)
    {
        try {
            Event::dispatch($event);
        } catch (\Error $e) {
            Log::debug("Module missing for event: " . get_class($event));
        } catch (\Exception $e) {
            Log::error("Event handling error: " . $e->getMessage());
        }
    }
    
    public function dispatchToModules($event, $targetModules = [])
    {
        foreach ($targetModules as $module) {
            if (ModuleRegistry::isAvailable($module)) {
                try {
                    $service = app(ModuleRegistry::getService($module));
                    $service->handleEvent($event);
                } catch (\Exception $e) {
                    Log::warning("Failed to dispatch event to {$module}: " . $e->getMessage());
                }
            }
        }
    }
}