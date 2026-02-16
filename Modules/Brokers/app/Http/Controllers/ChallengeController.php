<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;

use Modules\Brokers\Services\ChallengeCategoryService;
use Modules\Brokers\Transformers\ChallengeCategoryResource;


use Modules\Brokers\Services\ChallengeService;
use Illuminate\Validation\ValidationException;
use Modules\Brokers\Repositories\MatrixHeaderRepository;
use Modules\Brokers\Transformers\MatrixHeaderResource;
use Modules\Brokers\Enums\ChallengeTabEnum;

class ChallengeController extends Controller
{
    protected ChallengeCategoryService $challengeCategoryService;
    protected ChallengeService $challengeService;

    public function __construct(ChallengeCategoryService $challengeCategoryService, ChallengeService $challengeService)
    {
        $this->challengeCategoryService = $challengeCategoryService;
        $this->challengeService = $challengeService;
    }

    /**
     * Refactored show method using helper methods from ChallengeService
     */
    public function show(Request $request, int $broker_id): JsonResponse
    {
      
        try {
          
            
           $validatedData=$request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            'amount_id' => 'required|integer|exists:challenge_amounts,id',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ]);
            

           $amountId= $validatedData['amount_id'];
           $zoneId = $validatedData['zone_id'] ?? null;
           $categoryId=$validatedData['category_id'];
           $stepId=$validatedData['step_id'];

           //the chalenge row for the current parameters
           $challenge=  $this->challengeService->findChallengeByParams(
            false,
            $categoryId,
            $stepId,
            $amountId,
            $broker_id,
            $zoneId
        );
        $chId=$challenge?->id??null;
        
        $responseData=$this->challengeService->getChallengeData($chId, $broker_id, false, $zoneId);
        
        $this->challengeService->addPlaceholderData($responseData, $broker_id,$categoryId, $stepId, $zoneId);
        
        return response()->json([
            'success' => true,
            'data' => array_filter($responseData, fn($v) => $v !== null)
        ]);
        
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve challenge data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showPlaceholders(Request $request): JsonResponse
    {
    
        try {             
            $validatedData=$request->validate([
             'category_id' => 'required|integer|exists:challenge_categories,id',
             'step_id' => 'required|integer|exists:challenge_steps,id',
             'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
         ]);
             
            $amountId= null;//for placeholders, the amount id is null
  
            $zoneId = $validatedData['zone_id'] ?? null;
            $categoryId=$validatedData['category_id'];
            $stepId=$validatedData['step_id'];
 
            //the chalenge row for the current parameters
            $challenge=  $this->challengeService->findChallengeByParams(
             true,
             $categoryId,
             $stepId,
             $amountId,
             null,
             $zoneId
         );
         $chId=$challenge?->id??null;
         
         $responseData=$this->challengeService->getChallengeData($chId, null, true, $zoneId);
          
         return response()->json([
             'success' => true,
             'data' => array_filter($responseData, fn($v) => $v !== null)
         ]);
         
         } catch (\Throwable $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to retrieve challenge data',
                 'error' => $e->getMessage()
             ], 500);
         }
    }

    
    /**
     * Get the challenge categories for a broker
     * @param int $broker_id
     * @return JsonResponse
     * @throws \Exception
     */
    public function getChallengeCategories(Request $request, int $broker_id): JsonResponse
    {
        try {
            $challengeCategories = $this->challengeCategoryService->getChallengeCategories($broker_id);
            $response = [
                'success' => true,
                'data' => ChallengeCategoryResource::collection($challengeCategories),
            ];

            return response()->json($response);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve challenge categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



   

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $broker_id): JsonResponse
    {
        try { 
            $validatedData = $this->challengeService->validatePostRequestData($request);
           // $brokerId = $validatedData['broker_id']??null;
            $zoneId = $validatedData['zone_id'] ?? null;
            //$isAdmin = $validatedData['is_admin'] ?? null;
            $isPlaceholder = $validatedData['is_placeholder'];
          
            $isAdmin = app('isAdmin');
         
            //process the request and update the challenge matrix and extra data using transaction
            
           ['challenge_id' => $challenge_id] = $this->challengeService->processRequest($validatedData, $broker_id, $isPlaceholder, $isAdmin,$zoneId);

            //$responseData=$this->challengeService->getChallengeData($processResult['challenge_id'], $broker_id, $isPlaceholder, $zoneId);
           
            return response()->json([
                'success' => true,
                'message' => 'Challenge matrix with id '.$challenge_id.' created successfully',
                //'data' => $responseData
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create challenge matrix',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMatrixPlaceholders(Request $request): JsonResponse
    {

       
        try { 
       
        $validatedData=$request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            //'step_slug' => 'nullable|string',
            'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
            'matrix' => 'required|array',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ]);
            $zoneId = $validatedData['zone_id'] ?? null;
           
            //process the request and update the challenge matrix and extra data using transaction
            
           ['challenge_id' => $challenge_id] = $this->challengeService->processRequest($validatedData, null, true,true,$zoneId);

            //$responseData=$this->challengeService->getChallengeData($processResult['challenge_id'], $broker_id, $isPlaceholder, $zoneId);
           
            return response()->json([
                'success' => true,
                'message' => 'Challenge matrix with id '.$challenge_id.' created successfully',
               
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create challenge matrix',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDefaultChallengeCategories()
    {
        try {
            
            $challengeCategories = $this->challengeCategoryService->getChallengeCategories(null);

            $response = [
                'success' => true,
                'data' => ChallengeCategoryResource::collection($challengeCategories),
            ];

            return response()->json($response);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve challenge categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Get the challenge matrix headers for a broker
     * @param Request $request
     * @param MatrixHeaderRepository $rep
     * @return JsonResponse
     * @throws \Exception
     */
    public function getChallengeMatrixHeaders(Request $request, MatrixHeaderRepository $rep): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'col_group' => 'sometimes|string|max:145',
                'row_group' => 'sometimes|string|max:145',
                'language' => 'sometimes|string|max:15',
            ]);


            $columnHeaders =  MatrixHeaderResource::collection($rep->getHeadearsByType(
                'column',
                null,
                null,
                $validatedData['col_group'] ?? null,
                $validatedData['language'],
                false
            ));


            $rowHeaders = $rep->getHeadearsByType(
                'row',
                null,
                null,
                $validatedData['row_group'] ?? null,
                $validatedData['language'],
                false
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'columnHeaders' => $columnHeaders,
                    'rowHeaders' => MatrixHeaderResource::collection($rowHeaders)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get headers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a challenge category, step or amount
     * @param int $broker_id
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function removeChallengeTab(string $tab_type, int $broker_id, Request $request): JsonResponse
    {
        
        try {
            $validatedData = $request->validate([
                'category_id' => 'sometimes|integer|exists:challenge_categories,id',
                'step_id' => 'sometimes|integer|exists:challenge_steps,id',
                'amount_id' => 'sometimes|integer|exists:challenge_amounts,id',
            ]);
            $category_id = $validatedData['category_id']??null;
            $step_id = $validatedData['step_id']??null;
            $amount_id = $validatedData['amount_id']??null;

            $tab_type = ChallengeTabEnum::tryFrom($tab_type);
            if($tab_type === null){
                return response()->json(['success' => false, 'message' => 'Invalid tab type'], 400);
            }

            if($tab_type === ChallengeTabEnum::CATEGORY && $category_id){
                $this->challengeCategoryService->deleteChallengeCategory( $category_id,$broker_id);
            }
            if($tab_type === ChallengeTabEnum::STEP && $step_id){
                    $this->challengeCategoryService->deleteChallengeCategoryStep( $step_id,$broker_id);
                }
            if($tab_type === ChallengeTabEnum::AMOUNT && $amount_id){
                $this->challengeCategoryService->deleteChallengeCategoryAmount( $amount_id,$broker_id);
            }
            //  $this->challengeCategoryService->removeChallengeCategory($broker_id, $category_id);
            return response()->json(['success' => true, 'message' => 'Challenge tab of type '.$tab_type->value.' removed successfully']);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to remove challenge tab of type ', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a challenge tab
     * @param int $broker_id
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function addChallengeTab(string $tab_type, int $broker_id, Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'default_tab_id_to_clone' => 'required|string|max:145',
                'tab_order' => 'sometimes|integer',
                'broker_challenge_category_id' => 'sometimes|string|max:145',
            ]);
            $tab_type = ChallengeTabEnum::tryFrom($tab_type);
            if($tab_type === null){
                return response()->json(['success' => false, 'message' => 'Invalid tab type'], 400);
            }
            $default_tab_id_to_clone = $validatedData['default_tab_id_to_clone'];
            
            $tab_order = $validatedData['tab_order']??0;

            $broker_challenge_category_id = $validatedData['broker_challenge_category_id']??null;

            if($broker_challenge_category_id){
                $broker_challenge_category = $this->challengeCategoryService->getChallengeCategoryByIdAndBroker($broker_challenge_category_id, $broker_id);
                if(!$broker_challenge_category){
                    return response()->json(['success' => false, 'message' => 'Broker challenge category not found'], 404);
                }
            }
            $this->challengeCategoryService->addChallengeTabToBroker($tab_type, $default_tab_id_to_clone, $tab_order, $broker_id, $broker_challenge_category_id);
            return response()->json(['success' => true, 'message' => 'Challenge tab added successfully']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add challenge tab', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Save the order of the challenge tabs
     * @param Request $request
     * @param int $broker_id
     * @param string $tab_type
     * @return JsonResponse
     * @throws \Exception
     */
    public function saveChallengeTabOrder(Request $request, int $broker_id, string $tab_type): JsonResponse
    {
       
        try {
            $validatedData = $request->validate([
                'tab_ids' => 'required|array',
            ]);
            $tabs = $validatedData['tab_ids'];
            $tab_type = ChallengeTabEnum::tryFrom($tab_type);
            if($tab_type === null){
                return response()->json(['success' => false, 'message' => 'Invalid tab type'], 400);
            }
            $result = $this->challengeCategoryService->saveChallengeTabOrder($tabs, $broker_id, $tab_type);
            if(!$result){
                return response()->json(['success' => false, 'message' => 'Failed to save challenge tab order'], 500);
            }
            return response()->json(['success' => true, 'message' => 'Challenge tab order saved successfully']);
        }
        catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to save challenge tab order', 'error' => $e->getMessage()], 500);
        }
    }
}
