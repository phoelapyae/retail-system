<?php

namespace App\Core\Interfaces;

use App\Core\Events\ModuleEvent;

interface ModuleServiceInterface
{
    public function handleEvent(ModuleEvent $event);
    public function isHealthy(): bool;
    public function getModuleName(): string;
}