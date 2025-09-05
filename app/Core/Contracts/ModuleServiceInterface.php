<?php

namespace App\Core\Contracts;

use App\Core\Events\ModuleEvent;

interface ModuleServiceInterface
{
    public function handleEvent(ModuleEvent $event);
    public function isHealthy(): bool;
}