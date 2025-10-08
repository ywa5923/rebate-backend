<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetIsAdminFlag
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (App::environment('local')) {
            $isAdmin = true; // or false, depending on your test case
        } else {
            //TODO: Implement the logic to check if the user is an admin
           // $isAdmin = Auth::check() && Auth::user()->is_admin; // Adjust to match your DB field
        }

        // Share it globally to all views
       // View::share('isAdmin', $isAdmin);

        // Also store in the app container (optional)
        app()->instance('isAdmin', $isAdmin);

        return $next($request);
    }
}
