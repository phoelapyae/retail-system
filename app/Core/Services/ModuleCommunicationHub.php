<?php

namespace App\Core\Services;

class ModuleCommunicationHub
{
    private $dispatcher;
    
    public function __construct(SafeEventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    public function notify($eventClass, $data, $targetModules = [])
    {
        $event = new $eventClass($data);
        
        if (empty($targetModules)) {
            $this->dispatcher->dispatch($event);
        } else {
            $this->dispatcher->dispatchToModules($event, $targetModules);
        }
    }
    
    public function request($moduleName, $method, $params = [], $default = null)
    {
        if (!ModuleRegistry::isAvailable($moduleName)) {
            return $default;
        }
        
        try {
            $service = app(ModuleRegistry::getService($moduleName));
            return $service->$method(...$params);
        } catch (\Exception $e) {
            \Log::warning("Module request failed: {$moduleName}::{$method}");
            return $default;
        }
    }
    
    public function getAllModuleStatus()
    {
        $status = [];
        foreach (ModuleRegistry::getAvailableModules() as $module) {
            $serviceClass = ModuleRegistry::getService($module);
            $service = app($serviceClass);
            
            $status[$module] = [
                'available' => true,
                'healthy' => $service->isHealthy(),
                'service_class' => $serviceClass
            ];
        }
        
        return $status;
    }
}