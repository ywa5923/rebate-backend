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
use Modules\Brokers\Transformers\BrokerListResource;

//{{PATH}}/brokers?language[eq]=ro&page=1&columns[in]=position_list,short_payment_options&filters[in]=a,b,c

class BrokerController extends Controller
{
    

     public function __construct(protected BrokerService $brokerService)
     {
     }

    public function index(BrokersQueryParser $queryParser,Request $request)
    {
       return $this->brokerService->process($queryParser->parse($request));

        //tested with http://localhost:8000/api/v1/brokers?language[eq]=ro&page=1&columns[in]=trading_name,trading_fees,account_type,jurisdictions,promotion_title,fixed_spreads,support_options&order_by[eq]=+account_type
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
     * get broker context
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
    */
    public function getBrokerInfo(Request $request, $id)
    {
        try {
            return response()->json($this->brokerService->getBrokerContext($id));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get broker context',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    /**
     * Get broker list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function getBrokerList(Request $request)
    {
        try {
            // Validate all inputs
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'order_by' => 'nullable|string|in:id,is_active,broker_type,country,zone,trading_name,created_at,updated_at',
                'order_direction' => 'nullable|string|in:asc,desc',
                'broker_type' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:50',
                'zone' => 'nullable|string|max:50',
                'trading_name' => 'nullable|string|max:255',
            ]);
            
            $perPage = $validated['per_page'] ?? 15;
            $orderBy = $validated['order_by'] ?? 'id';
            $orderDirection = $validated['order_direction'] ?? 'asc';
            
            // Collect and sanitize filters
            $filters = [
                'broker_type' => !empty($validated['broker_type']) ? $this->sanitizeLikeInput($validated['broker_type']) : null,
                'country' => !empty($validated['country']) ? $this->sanitizeLikeInput($validated['country']) : null,
                'zone' => !empty($validated['zone']) ? $this->sanitizeLikeInput($validated['zone']) : null,
                'trading_name' => !empty($validated['trading_name']) ? $this->sanitizeLikeInput($validated['trading_name']) : null,
            ];
            
            $brokers = $this->brokerService->getBrokerList($perPage, $orderBy, $orderDirection, $filters);
            
            return response()->json([
                'success' => true,
                'data' => BrokerListResource::collection($brokers->items()),
                'pagination' => [
                    'current_page' => $brokers->currentPage(),
                    'last_page' => $brokers->lastPage(),
                    'per_page' => $brokers->perPage(),
                    'total' => $brokers->total(),
                    'from' => $brokers->firstItem(),
                    'to' => $brokers->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get broker list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function toggleActiveStatus(Request $request, $id)
    {
        try {
            return response()->json($this->brokerService->toggleActiveStatus($id));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle active status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sanitize input for LIKE queries by escaping special characters
     * @param string $input
     * @return string
     */
    private function sanitizeLikeInput(string $input): string
    {
        // Escape special LIKE characters: %, _, \
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $input);
    }
   
    
}
