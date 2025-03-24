<?php

namespace Modules\Translations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Translations\Models\Country;
use Modules\Translations\Services\ZoneQueryParser;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ZoneQueryParser $queryParser,Request $request)
    {

        $countryCondition = $queryParser->parse($request)->getWhereParam("country");
        $country = Country::with('zone')->where(...$countryCondition)->first();
        
        if (!$country || !$country->zone) {
            return new Response(['error' => 'Country not found'], 422);
        }
    
        return new Response(['zone' => $country->zone->zone_code], 200);
        
    }

}
