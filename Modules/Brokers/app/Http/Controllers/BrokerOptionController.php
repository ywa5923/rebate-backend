<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Repositories\BrokerOptionRepository;
use Modules\Brokers\Services\BrokerOptionQueryParser;
use Modules\Brokers\Transformers\BrokerOptionCollection;

class BrokerOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BrokerOptionQueryParser $queryParser,BrokerOptionRepository $rep, Request $request)
    {
        //{{PATH}}/broker_options?language[eq]=ro
        $parsedQuery=$queryParser->parse($request);
       
        if(!empty($parsedQuery["whereParams"])){
        //ex: ['language_code','=','ro']
        $languageParams=$parsedQuery["whereParams"][0];
        $collection=new BrokerOptionCollection($rep->translate($languageParams));
        return $collection;
        }else
        return new Response("not found",404);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('brokers::create');
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
        return view('brokers::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('brokers::edit');
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
