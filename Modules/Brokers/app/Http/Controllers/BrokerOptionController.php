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
use Modules\Brokers\Transformers\FormBrokerOptionResource;
use Modules\Brokers\Transformers\OptionCategoryResource;
use Modules\Brokers\Services\BrokerOptionService;
use Modules\Brokers\Http\Requests\BrokerOptionListRequest;
use Modules\Brokers\Http\Requests\StoreBrokerOptionRequest;
use Modules\Brokers\Http\Requests\UpdateBrokerOptionRequest;
use Modules\Brokers\Transformers\BrokerOptionResource;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Tables\BrokerOptionTableConfig;
use Modules\Brokers\Forms\BrokerOptionForm;
class BrokerOptionController extends Controller
{

  
    public function __construct(
        protected BrokerOptionService $brokerOptionService,
        private readonly BrokerOptionTableConfig $tableConfig,
        private readonly BrokerOptionForm $formConfig
    ){}

   
    /**
     * Require broker permission and at least view action.
     * Display a listing of the resource.
     * These options are formatted for the broker dashboard.
     * Return an arrat with option categories that includes the broker options.
     */
    public function index(BrokerOptionQueryParser $queryParser, BrokerOptionRepository $rep, Request $request)
    {
        
        //{{PATH}}/broker_options?language[eq]=ro
        $queryParser->parse($request);
        if (empty($queryParser->getWhereParams())) {
            return new Response("not found", 404);
        }
      
            //ex: ['language_code','=','ro']
            $languageParams = $queryParser->getWhereParam("language");

            try{
                $allColumns=$queryParser->getWhereParam("all_columns");
                $brokerType=$queryParser->getWhereParam("broker_type")[2]??null;
             
                if($allColumns)
                {
                   //return all options categories with all options
                   return OptionCategoryResource::collection($rep->getBrokerOptions($languageParams,1,$brokerType));
                    // return FormBrokerOptionResource::collection($rep->getBrokerOptions($languageParams));
                }

                $optionsCollection = new BrokerOptionCollection($rep->getDropdownOptions($languageParams));
               
                $filterRepo = new FilterRepository();
              
                $brokerExtColumns = $filterRepo->getSettingsParam("page_brokers", $languageParams)["broker_ext_columns"];
             
            }catch(\Exception $e){
                return response()->json(['error' => 'Error getting broker options'], 422);
            }catch(\JsonException $e){
                return new Response(["error" => "Json decoding error broker options"], 404);
            }

            $options = [];
            $slugOptions = [];
            $dropdownOptions = [];
            $defaultLoadedOptions = [];
            $ratingOptions = [];
            $allowSortingOptions = [];
            $booleanOptions = [];

            foreach ($optionsCollection->resolve() as $brokerOption) {
                $slug = array_key_first($brokerOption);
                $options[] = $brokerOption;
                if ($brokerOption["form_type"] == "rating") {
                    $ratingOptions[$slug] = $brokerOption[$slug];
                }
                if ($brokerOption["allow_sorting"] == true) {

                    $allowSortingOptions[$slug] = $brokerOption[$slug];
                }
                if ($brokerOption["data_type"] == "boolean") {
                    $booleanOptions[$slug] = $brokerOption[$slug];
                }
                if ($brokerOption["load_in_dropdown"] == true) {
                    $dropdownOptions[] = $brokerOption;
                }
                if ($brokerOption["default_loading"] == true) {
                    $defaultLoadedOptions[] = $brokerOption;
                }
            }

          
            usort($dropdownOptions, fn($a, $b) => $a['dropdown_position'] <=> $b['dropdown_position']);
            usort($defaultLoadedOptions, fn($a, $b) => $a['default_loading_position'] <=> $b['default_loading_position']);

            $defaultLoadedOptions = array_merge(...array_map(function ($option) {
                $slug = array_key_first($option);
                return [$slug => $option[$slug]];
            }, $defaultLoadedOptions));
            $dropdownOptions = array_merge(...array_map(function ($option) {
                $slug = array_key_first($option);
                return [$slug => $option[$slug]];
            }, $dropdownOptions));
           
            $dropdownOptions = $brokerExtColumns + $dropdownOptions;

            // return $collection;
            return new Response(json_encode([
                "options" => $dropdownOptions,
                //"defaultLoadedOptions"=>array_values($defaultLoadedOptions)
                "defaultLoadedOptions" => $defaultLoadedOptions,
                "allowSortingOptions" => $allowSortingOptions,
                "booleanOptions" => $booleanOptions,
                "ratingOptions" => $ratingOptions

            ]), 200);
        
            
    }

    /**
     * Get broker options.
     * Auth is done in the BrokerOptionListRequest::authorize() method
     * These options are formatted for the super admin dashboard.
     * Return a collection of broker options.
     * @param BrokerOptionRequest $request
     * @return Response
     */
    public function getBrokerOptionsList(BrokerOptionListRequest $request): Response
    {
       
        try{
            $filters = $request->getFilters();
            $orderBy = $request->getOrderBy();
            
            $orderDirection = $request->getOrderDirection();
            $perPage = $request->getPerPage();
            
            $brokerOptions = $this->brokerOptionService->getAllBrokerOptions($filters, $orderBy, $orderDirection, $perPage);
            
         
            
            return new Response(json_encode([
                'success' => true,
                'data'=> (new BrokerOptionCollection($brokerOptions->items(), ['detail' => 'table-list'])),
                'form_config'=> $this->formConfig->getFormData(),
                'table_columns_config' => $this->tableConfig->columns(),
                'filters_config'=>$this->tableConfig->filters(),
                'pagination' => [
                    'current_page' => $brokerOptions->currentPage(),
                    'last_page' => $brokerOptions->lastPage(),
                    'per_page' => $brokerOptions->perPage(),
                    'total' => $brokerOptions->total(),
                    'from' => $brokerOptions->firstItem(),
                    'to' => $brokerOptions->lastItem()
                ],
                

            ]), 200);
        }catch(\Exception $e){
            return new Response(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]), 422);
        }
    }

    

    /**
     * Display the specified broker option.
     * Only super admin can access this action
     * Auth is done in RequireSuperAdmin middleware
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        
       
        try {
            $brokerOption = $this->brokerOptionService->getBrokerOptionById($id);
            
            if (!$brokerOption) {
                return response()->json([
                    'success' => false,
                    'message' => 'Broker option not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => (new BrokerOptionResource($brokerOption))->additional(['detail' => 'form-edit']),
                //'form' => $form->getFormData()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving broker option',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getFormConfig(): JsonResponse
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
     * Store a newly created broker option.
     */
    public function store(StoreBrokerOptionRequest $request): JsonResponse
    {
        
        try {
            $data = $request->validated();
            $brokerOption = $this->brokerOptionService->createBrokerOption($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Broker option created successfully',
                'data' => (new BrokerOptionResource($brokerOption))->additional(['detail' => true])
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create broker option',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update the specified broker option.
     */
    public function update(UpdateBrokerOptionRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();

            
          
            $brokerOption = $this->brokerOptionService->updateBrokerOption($data, $id);
            
            return response()->json([
                'success' => true,
                'message' => 'Broker option updated successfully',
                'data' => (new BrokerOptionResource($brokerOption))->additional(['detail' => true])
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Broker option not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update broker option',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $this->brokerOptionService->deleteBrokerOption($id);
            return response()->json(['success' => true, 'message' => 'Broker option deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete broker option', 'error' => $e->getMessage()], 422);
        }
    }
    
}
