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
       $queryParser->parse($request);
       
        if(!empty($queryParser->getWhereParams())){
        //ex: ['language_code','=','ro']
        $languageParams=$queryParser->getWhereParam("language");
        $collection=new BrokerOptionCollection($rep->translate($languageParams));
        $options=[];
        $slugOptions=[];
        $defaultLoadedOptions=[];
        foreach($collection->resolve() as $brokerOption)
        {
            $options[]=$brokerOption;
        }
        //dd($options);
        $dropdownOptions=array_filter($options, function($option){
            return $option['default_loading']==false;
        });
        $defaultLoadedOptions=array_filter($options, function($option){
            return $option['default_loading']==true;
        });


        usort($dropdownOptions, fn($a, $b) => $a['dropdown_position'] <=> $b['dropdown_position']); 
        usort($defaultLoadedOptions, fn($a, $b) => $a['default_loading_position'] <=> $b['default_loading_position']); 

        $defaultLoadedOptions=array_merge(...array_map(function ($option) {
            $slug=array_key_first($option);
            return [$slug=>$option[$slug]];
        }, $defaultLoadedOptions));
        $dropdownOptions=array_merge(...array_map(function ($option) {
            $slug=array_key_first($option);
            return [$slug=>$option[$slug]];
        }, $dropdownOptions)); 
      
       // return $collection;
       return new Response(json_encode([
        "options"=> $dropdownOptions,
        //"defaultLoadedOptions"=>array_values($defaultLoadedOptions)
        "defaultLoadedOptions"=> $defaultLoadedOptions

    ]),200);
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
