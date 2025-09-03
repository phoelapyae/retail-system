<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Core\Services\ModuleRegistry;

class ModuleEnabledMiddleware
{
    public function handle(Request $request, Closure $next, $module)
    {
        if (!ModuleRegistry::isAvailable($module)) {
            return response()->json([
                'success' => false,
                'message' => "Module '{$module}' is not available"
            ], 404);
        }
        
        return $next($request);
    }
}