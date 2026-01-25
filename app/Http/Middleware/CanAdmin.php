<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Modules\Brokers\Models\Broker;
use Modules\Countries\Models\Country;
use Modules\Zones\Models\Zone;
use App\Utilities\ModelHelper;
use Modules\Brokers\Models\AccountType;
class CanAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $resourceClass, $routeParamKey): Response
    {
        $isAdmin = false;
        $countryId = null;
        $zoneId = null;
        $brokerId = null;
        if (App::environment('local')) {
            //$isAdmin = true; // or false, depending on your test case
          
        }
        $resourceClassName=ModelHelper::getModelClassFromSlug($resourceClass);
        $user=$request->user();
        $resourceId = $request->route($routeParamKey);
        $resource = $resourceClassName::find($resourceId);

        if (!$resource) {
            return response()->json(['success' => false, 'message' => 'Resource not found'], 404);
        }
        if($resource instanceof Broker) {
            $brokerId = $resource->id;
            $country=$resource->country;
            $countryId = $country?->id;
            $zoneId = $country?->zone?->id;
        } else if($resource instanceof AccountType) {
            $brokerId = $resource->broker->id;
            $country = $resource->broker->country;
            $countryId = $country?->id;
            $zoneId = $country?->zone?->id;
        }


        if($user->tokenCan('zone:manage:'.$zoneId) || $user->tokenCan('zone:edit:'.$zoneId)) {
            $isAdmin = true;
        } elseif($user->tokenCan('country:manage:'.$countryId) || $user->tokenCan('country:edit:'.$countryId)) {
            $isAdmin = true;
        }

        app()->instance('isAdmin', $isAdmin);

        if($isAdmin || $user->tokenCan('broker:manage:'.$brokerId) || $user->tokenCan('broker:edit:'.$brokerId)) {
            return $next($request);
        }else{
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        // Share it globally to all views
       // View::share('isAdmin', $isAdmin);
       
        
    }
}
