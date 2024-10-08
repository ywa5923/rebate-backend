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

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Brokers\Services\BrokerQueryParser;
use Modules\Brokers\Services\BrokerService;

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

    public function index(BrokerQueryParser $queryParser,BrokerService $brokerService,Request $request)
    {
      //dd($queryParser->parse($request)->getArrayResults());
      //dd($queryParser->parse($request)->getWhereInParam("filter_offices"));
       return $brokerService->process($queryParser->parse($request));

        //brokerService->process($queryParams,repository)

        //get brokers registered with  default language = $language

       // $defaultLanguageBrokers= Broker::with('dynamicOptionsValues')->where('default_language',  $language)->get();

        //get brokers registered with other default language and was translated to $language by AI

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
     *             @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="favicon", type="string"),
     *             @OA\Property(property="trading_name", type="string"),
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
    }

    /**
     * @OA\Get(
     *     path="/api/v1/broker/{id}",
     *     tags={"Broker"},
     *     summary="Show a broker",
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
    public function show($id)
    {
        return view('brokers::show');
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="favicon", type="string"),
     *             @OA\Property(property="trading_name", type="string"),
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
        //
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/broker/{id}",
     *     tags={"Broker"},
     *     summary="Delete broker",
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
