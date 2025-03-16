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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('translations::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('translations::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('translations::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
