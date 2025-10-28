<?php

namespace Modules\Translations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Translations\Models\Country;
use Modules\Translations\Utilities\ZoneQueryParser;

class ZoneController extends Controller
{
    /**
     * Get zone by country (legacy endpoint)
     * This returns the zone_code for a given country
     */
    public function index(ZoneQueryParser $queryParser, Request $request)
    {
        $countryCondition = $queryParser->parse($request)->getWhereParam("country");
        $country = Country::with('zone')->where(...$countryCondition)->first();
        
        if (!$country || !$country->zone) {
            return new Response(['error' => 'Country not found'], 422);
        }
    
        return new Response(['zone' => $country->zone->zone_code], 200);
    }
}
