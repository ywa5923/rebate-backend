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
use Modules\Brokers\Repositories\FilterRepository;

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
       
        $optionsCollection=new BrokerOptionCollection($rep->translate($languageParams));
        $filterRepo = new FilterRepository();
       
        
        $brokerExtColumns=$filterRepo->getSettingsParam("page_brokers", $languageParams)["broker_ext_columns"];
        

        $options=[];
        $slugOptions=[];
        $dropdownOptions=[];
        $defaultLoadedOptions=[];
        $ratingOptions=[];
        $allowSortingOptions=[];
        $booleanOptions=[];

        foreach($optionsCollection->resolve() as $brokerOption)
        {
            $slug=array_key_first($brokerOption);
            $options[]=$brokerOption;
            if($brokerOption["form_type"]=="rating"){
                $ratingOptions[$slug]=$brokerOption[$slug];
            }
             if($brokerOption["allow_sorting"]==true){

                $allowSortingOptions[$slug]=$brokerOption[$slug];
            }
            if($brokerOption["data_type"]=="boolean"){
                $booleanOptions[$slug]=$brokerOption[$slug];
            }
            if($brokerOption["load_in_dropdown"]==true){
                $dropdownOptions[]=$brokerOption;
            }
            if($brokerOption["default_loading"]==true){
                    $defaultLoadedOptions[]=$brokerOption;
            }
        }
       
        // $dropdownOptions=array_filter($options, function($option){
        //     return $option['load_in_dropdown']==true;
        // });
        // $defaultLoadedOptions=array_filter($options, function($option){
        //     return $option['default_loading']==true;
        // });
        // $allowSortingOptions=array_filter($options, function($option){
        //     return $option['allow_sorting']==true;
        // });
        // $booleanOptions=array_filter($options, function($option){
        //     return $option['data_type']=="boolean";
        // });

       
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
        // $allowSortingOptions=array_merge(...array_map(function ($option) {
        //     $slug=array_key_first($option);
        //     return [$slug=>$option[$slug]];
        // }, $allowSortingOptions)); 

        // $booleanOptions=array_merge(...array_map(function ($option) {
        //     $slug=array_key_first($option);
        //     return [$slug=>$option[$slug]];
        // },  $booleanOptions)); 
      
        //add ext relation column to be shown in dropdown
        $dropdownOptions=$brokerExtColumns+$dropdownOptions;
        
       // return $collection;
       return new Response(json_encode([
        "options"=> $dropdownOptions,
        //"defaultLoadedOptions"=>array_values($defaultLoadedOptions)
        "defaultLoadedOptions"=> $defaultLoadedOptions,
        "allowSortingOptions"=>$allowSortingOptions,
        "booleanOptions"=>$booleanOptions,
        "ratingOptions"=>$ratingOptions

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
