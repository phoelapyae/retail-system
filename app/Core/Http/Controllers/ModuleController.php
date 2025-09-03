<?php

namespace App\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Services\{ModuleRegistry, ModuleCommunicationHub, ModuleHealthChecker};
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    private $hub;
    private $healthChecker;
    
    public function __construct(ModuleCommunicationHub $hub, ModuleHealthChecker $healthChecker)
    {
        $this->hub = $hub;
        $this->healthChecker = $healthChecker;
    }
    
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'available_modules' => ModuleRegistry::getAvailableModules(),
                'module_status' => $this->hub->getAllModuleStatus()
            ]
        ]);
    }
    
    public function health()
    {
        return response()->json([
            'success' => true,
            'data' => $this->healthChecker->checkAllModules()
        ]);
    }
    
    public function metrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->healthChecker->getModuleMetrics()
        ]);
    }
}