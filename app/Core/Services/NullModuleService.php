<?php

namespace App\Core\Services;

use App\Core\Events\ModuleEvent;
use App\Core\Contracts\ModuleServiceInterface;

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
}