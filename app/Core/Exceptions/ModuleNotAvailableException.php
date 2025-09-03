<?php

namespace App\Core\Exceptions;

use Exception;

class ModuleNotAvailableException extends Exception
{
    public function __construct($moduleName)
    {
        parent::__construct("Module '{$moduleName}' is not available or not enabled.");
    }
}