<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //abort_if(!$request->user()?->tokenCan('*'), 403);
        $user = $request->user(); // requires auth:sanctum BEFORE this middleware
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated123'], 401);
        }
        if (!$user->tokenCan('*')) {
            return response()->json(['success' => false, 'message' => 'Forbidden123'], 403);
        }
       
        return $next($request);
    }
}
