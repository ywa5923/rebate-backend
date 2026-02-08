<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Illuminate\Support\Facades\Validator;
use Modules\Brokers\Services\BrokersQueryParser;
use Modules\Brokers\Services\BrokerService;
use Modules\Translations\Models\Zone;
use Modules\Brokers\Models\Setting;
use Modules\Brokers\Transformers\BrokerListResource;
use Modules\Brokers\Http\Requests\BrokerListRequest;
use Modules\Brokers\Transformers\BrokerTypeResource;
use Modules\Brokers\Transformers\CountryCollection;
use Modules\Brokers\Models\Country;
use Modules\Brokers\Tables\BrokerTableConfig;
use Modules\Brokers\Forms\BrokerForm;
use Modules\Brokers\Http\Requests\BrokerToggleActiveRequest;
use Modules\Brokers\Services\ChallengeService;
use Modules\Auth\Services\BrokerTeamService;
use Modules\Auth\Services\UserPermissionService;
use Modules\Auth\Services\MagicLinkService;
use Illuminate\Contracts\Mail\Mailer;
use Modules\Auth\Models\BrokerTeamUser;
use Modules\Auth\Mail\MagicLinkMail;
use Modules\Auth\Enums\AuthPermission;
use Modules\Auth\Enums\AuthAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Brokers\Http\Requests\RegisterBrokerRequest;
use Modules\Brokers\Enums\BrokerType as BrokerTypeEnum;

//{{PATH}}/brokers?language[eq]=ro&page=1&columns[in]=position_list,short_payment_options&filters[in]=a,b,c

class BrokerController extends Controller
{

    public function __construct(
        protected BrokerService $brokerService,
        private readonly BrokerTableConfig $tableConfig,
        private readonly BrokerForm $formConfig,
        protected ChallengeService $challengeService,
        protected BrokerTeamService $teamService,
        protected UserPermissionService $permissionService,
        protected MagicLinkService $magicLinkService,
        protected Mailer $mailService,
    ) {}

    public function index(BrokersQueryParser $queryParser, Request $request)
    {
        return $this->brokerService->process($queryParser->parse($request));

        //tested with http://localhost:8000/api/v1/brokers?language[eq]=ro&page=1&columns[in]=trading_name,trading_fees,account_type,jurisdictions,promotion_title,fixed_spreads,support_options&order_by[eq]=+account_type
    }

     /**
     * OK
     * Register a new broker
     */
    public function registerBroker(RegisterBrokerRequest $request)
    {
        try {
            // Create the broker
            $broker = DB::transaction(function () use ($request) {
                $data = $request->validated();
                $broker = $this->brokerService->registerBroker($data['trading_name'], $data['broker_type_id'], $data['country_id']);
                //create a default team for the broker and add a user in that team
                $team = $this->teamService->createTeam([
                    'broker_id' => $broker->id,
                    'name' => 'Default Team',
                    'description' => 'Default team for the broker',
                    'permissions' => [],
                ]);
                $user = $this->teamService->createTeamUser([
                    'broker_team_id' => $team->id,
                    'name' => 'Broker Admin',
                    'email' => $data['email'],
                    'is_active' => true,
                ]);

                //generate user permission for the user
                $this->permissionService->createPermission([
                    'subject_type' => BrokerTeamUser::class,
                    'subject_id' => $user->id,
                    'resource_id' => $broker->id,
                    'resource_value' => $data['trading_name'],
                ],AuthPermission::BROKER,AuthAction::MANAGE);

                //load default challenge categories for the broker
                if($broker->brokerType->name == BrokerTypeEnum::PROP_FIRM->value) {
                    $this->challengeService->cloneDefaultChallengesToBroker($broker->id);
                }


                //generate magic link for broker
                $magicLink = $this->magicLinkService->generateForTeamUser(
                    $user,
                    'registration',
                    ['requested_at' => now()],
                    96 // 96 hours = 4 days
                );

                //send email with magic link
               $this->mailService->to($user->email)->send(new MagicLinkMail($magicLink));

                return $broker;
            });

            // Load the broker type relationship
            //$broker->load('brokerType', 'dynamicOptionsValues');

            return response()->json([
                'success' => true,
                'message' => 'Broker registered successfully',
                'data' => [
                    'broker' => $broker,
                    'broker_type' => $broker->brokerType->name
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Broker registration failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to register broker',
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function show($id, Request $request)
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
        $tab = $request->query('tab')['eq'] ?? "all";

        $zone = Zone::where("countries", "like", "%$country%")->first()->zone_code;
        if ($tab == "all") {
            //return all options
        } else {
            $setting = Setting::where('key', "tab_" . $tab)->firstOrFail();
            //return options of a specific tab
            $tabData = json_decode($setting->value, 1);
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
     * Get form config
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormConfig()
    {
       
        try {
            return response()->json([
                'success' => true,
                'data' => $this->formConfig->getFormData()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting form data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broker list
     * This action is used in dashboards so we don't need multilanguage support
     * Auth is done in the BrokerListRequest::authorize() method
     * @param BrokerListRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBrokerList(BrokerListRequest $request, ?int $zone_id, ?int $country_id=null)
    {
      //TO DO 1: Get table filters as a function of logged user permissions
      //If the user is a platform user he should see only brokers that he has access to
      //TO DO 2: Add zone_id and country_id to filters
     // $filters['zone_id'] = $zone_id;
     // $filters['country_id'] = $country_id;
        try {

            $filters = $request->getFilters();
           
            $orderBy = $request->getOrderBy();
            $orderDirection = $request->getOrderDirection();
            $perPage = $request->getPerPage();

            // $perPage = $validated['per_page'] ?? 15;
            // $orderBy = $validated['order_by'] ?? 'id';
            // $orderDirection = $validated['order_direction'] ?? 'asc';

            // // Collect and sanitize filters
            // $filters = [
            //     'broker_type' => !empty($validated['broker_type']) ? $this->sanitizeLikeInput($validated['broker_type']) : null,
            //     'country' => !empty($validated['country']) ? $this->sanitizeLikeInput($validated['country']) : null,
            //     'zone' => !empty($validated['zone']) ? $this->sanitizeLikeInput($validated['zone']) : null,
            //     'trading_name' => !empty($validated['trading_name']) ? $this->sanitizeLikeInput($validated['trading_name']) : null,
            //     'is_active' => $validated['is_active'] ?? null,
            // ];

            $brokers = $this->brokerService->getBrokerList($perPage, $orderBy, $orderDirection, $filters);

            return response()->json([
                'success' => true,
                'data' => BrokerListResource::collection($brokers->items()),
                'table_columns_config' => $this->tableConfig->columns(),
                'filters_config' => $this->tableConfig->filters(),
                'form_config' => $this->formConfig->getFormData(),
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


    /**
     * Toggle active status of a broker
     * @param Request $request
     * @param Broker $broker
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActiveStatus(BrokerToggleActiveRequest $request, Broker $broker)
    {
       
        try {
            return response()->json($this->brokerService->toggleActiveStatus($broker->id));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle active status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getBrokerTypesAndCountries()
    {
        try {
            $countries = Country::all();
            $brokerTypes = BrokerType::all();
            return response()->json([
                'success' => true,
                'message' => 'Broker types and countries fetched successfully',
                'countries' => new CountryCollection($countries, ['minimal' => true]),
                'brokerTypes' => BrokerTypeResource::collection($brokerTypes),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get broker types and countries',
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
