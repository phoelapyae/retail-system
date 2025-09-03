<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JsonResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        
        return $next($request);
    }
}