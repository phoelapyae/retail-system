<?php

namespace App\Core\Interfaces;

use App\Core\Interfaces\ModuleServiceInterface;
use App\Core\Events\ModuleEvent;

class NullModuleService implements ModuleServiceInterface
{
    public function handleEvent(ModuleEvent $event)
    {
        return null;
    }
    
    public function isHealthy(): bool 
    {
        return false;
    }
    
    public function getModuleName(): string
    {
        return 'null';
    }
}