<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DynamicOptionsCategory;
use App\Models\DynamicOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\BrokerOptionsCategory;
use Modules\Brokers\Models\BrokerOptionsValue;
use Modules\Brokers\Models\BrokerType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Services\BrokersQueryParser;
use Modules\Brokers\Services\BrokerService;
use Modules\Brokers\Services\BrokerQueryParser;
use Modules\Translations\Models\Zone;
use Modules\Brokers\Models\Setting;

//{{PATH}}/brokers?language[eq]=ro&page=1&columns[in]=position_list,short_payment_options&filters[in]=a,b,c

class BrokerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/brokers/",
     *     tags={"Broker"},
     *     summary="Get all brokers",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="These request do not match our records"
     *     )
     * )
     */

     public function __construct()
     {
     }

    public function index(BrokersQueryParser $queryParser,BrokerService $brokerService,Request $request)
    {

     
      //dd($queryParser->parse($request)->getWhereInParam("filter_offices"));
       return $brokerService->process($queryParser->parse($request));

        //tested with http://localhost:8000/api/v1/brokers?language[eq]=ro&page=1&columns[in]=trading_name,trading_fees,account_type,jurisdictions,promotion_title,fixed_spreads,support_options&order_by[eq]=+account_type

    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     return view('brokers::create');
    // }

    /**
     * @OA\Post(
     *     path="/api/v1/brokers",
     *     tags={"Broker"},
     *     summary="Add a new broker",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="registration_language", type="string", example="en"),
     *             @OA\Property(property="registration_zone", type="string", example="US"),
     *             @OA\Property(property="broker_type_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="These credentials do not match our records"
     *     )
     * )
     */
    public function store(Request $request): RedirectResponse
    {
        //return new Response("not found", 404);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/broker/{id}",
     *     tags={"Broker"},
     *     summary="Show a broker",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Broker ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="These request do not match our records"
     *     )
     * )
     */
    public function show($id,Request $request)
    {
      
    $validator = Validator::make($request->query(), [
        'language.eq' => 'required|string',
        'country.eq' => 'required|string',
        'tab.eq' => 'nullable|string|in:reviews,default',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    $brokerData = Broker::findOrFail($id);
    // Retrieve query parameters
    $lang = $request->query('language')['eq']; // Fetch `lang[eq]`
    $country = $request->query('country')['eq'];
    $tab = $request->query('tab')['eq']??"all";

    $zone=Zone::where("countries","like","%$country%")->first()->zone_code;
    if($tab=="all"){
        //return all options
    }else{
        $setting=Setting::where('key',"tab_".$tab)->firstOrFail();
       //return options of a specific tab
       $tabData=json_decode($setting->value,1);
       if (!empty($tabData['options'])) {
        $options = $tabData['options'];
    
        // If 'relations' is absent, set to null, otherwise use the value
        $relations = $tabData['relations'] ?? null;
         
        //dd($options,$relations);

    } else {
        return response()->json([
            'error' => "The required keys 'options' or 'relations' are missing in the settings tab for key={$tab}.",
        ], 422);
    }
        
    }


    $additionalInfo = [
        'language' => $lang ?? 'default',
        'zone' => $zone ?? 'default',
        'tab' => $tab ?? 'default',
    ];

    return response()->json([
        'broker' => $brokerData,
        'additional_info' => $additionalInfo,
    ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit($id)
    // {
    //     return view('brokers::edit');
    // }

    /**
     * @OA\Put(
     *     path="/api/v1/broker/{id}",
     *     tags={"Broker"},
     *     summary="Update broker",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Broker ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(property="registration_language", type="string", example="en"),
     *             @OA\Property(property="registration_zone", type="string", example="US"),
     *             @OA\Property(property="broker_type_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="These credentials do not match our records"
     *     )
     * )
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //return new Response("not found", 404);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/broker/{id}",
     *     tags={"Broker"},
     *     summary="Delete broker",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Broker ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function destroy($id)
    {
        //
    }

    public function filter()
    {
        return "ok";
    }
}
