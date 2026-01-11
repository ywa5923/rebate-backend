<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;

use Modules\Brokers\Services\ChallengeCategoryService;
use Modules\Brokers\Transformers\ChallengeCategoryResource;


use Modules\Brokers\Services\ChallengeService;
use Illuminate\Validation\ValidationException;

class ChallengeController extends Controller
{
    protected ChallengeCategoryService $challengeCategoryService;
    protected ChallengeService $challengeService;

    protected bool $isAdmin;
    public function __construct(ChallengeCategoryService $challengeCategoryService, ChallengeService $challengeService)
    {
        $this->challengeCategoryService = $challengeCategoryService;
        $this->challengeService = $challengeService;
        $this->isAdmin = app('isAdmin');
    }

    /**
     * Refactored show method using helper methods from ChallengeService
     */
    public function show(Request $request): JsonResponse
    {
        //to do
        //check if the logged in user can view broker challenge data
        try {
           // $validatedData = $this->challengeService->validateGetRequestData($request);
            
           $validatedData=$request->validate([
            'category_id' => 'required|integer|exists:challenge_categories,id',
            'step_id' => 'required|integer|exists:challenge_steps,id',
            'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
            'is_placeholder' => 'required|boolean',
            'broker_id' => 'sometimes|nullable|integer|exists:brokers,id',
            'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
        ]);
            
           // for placeholder mode, the amount id is null,we return the matrix placeholders array and placeholder data for evaluation cost discount,
           //  affiliate link and affiliate master link
           //so in placeholder mode we return only placeholder data useful for admin only to add/edit placeholders
           //these placeholders are shown in broker's dashboard  challenge page when data is empty
           $isPlaceholder=$validatedData['is_placeholder']?true:false;
           $amountId= $isPlaceholder ?null:$validatedData['amount_id'];
           $brokerId=$validatedData['broker_id'] ?? null;
           $zoneId = $validatedData['zone_id'] ?? null;
           $categoryId=$validatedData['category_id'];
           $stepId=$validatedData['step_id'];

           //the chalenge row for the current parameters
           $challenge=  $this->challengeService->findChallengeByParams(
            $isPlaceholder,
            $categoryId,
            $stepId,
            $amountId,
            $brokerId,
            $zoneId
        );
        $chId=$challenge?->id??null;
        
        $responseData=$this->challengeService->getChallengeData($chId, $brokerId, $isPlaceholder, $zoneId);
        
        if(!$isPlaceholder){
            //if not placeholder mode, add the placeholder data for matrix and matrix extradata:affiliate link, affiliate master link, evaluation cost discount
            $this->challengeService->addPlaceholderData($responseData, $categoryId, $stepId, $zoneId);
        }
        
        return response()->json([
            'success' => true,
            'data' => array_filter($responseData, fn($v) => $v !== null)
        ]);
        

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve challenge data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    

    /**
     * @OA\Get(
     *     path="/api/v1/challenge-categories",
     *     tags={"ChallengeCategory"},
     *     summary="Get all challenge categories",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ChallengeCategory")),
     *             @OA\Property(property="pagination", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="total", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function getChallengeCategories(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' =>  'sometimes|string|max:255',
                'created_from' => 'sometimes|date',
                'created_to' => 'sometimes|date',
                'is_active' => 'sometimes|boolean',
                'sort_by' => 'sometimes|string|in:id,name,is_active,created_at,updated_at',
                'sort_direction' => 'sometimes|string|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            $challengeCategories = $this->challengeCategoryService->getChallengeCategories($request);

            $response = [
                'success' => true,
                'data' => ChallengeCategoryResource::collection($challengeCategories),
            ];

            if ($request->has('per_page')) {
                $response['pagination'] = [
                    'current_page' => $challengeCategories->currentPage(),
                    'last_page' => $challengeCategories->lastPage(),
                    'per_page' => $challengeCategories->perPage(),
                    'total' => $challengeCategories->total(),
                ];
            }

            return response()->json($response);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
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
    public function store(Request $request): JsonResponse
    {
        try { 
            $validatedData = $this->challengeService->validatePostRequestData($request);
            $brokerId = $validatedData['broker_id']??null;
            $zoneId = $validatedData['zone_id'] ?? null;
            $isAdmin = $validatedData['is_admin'] ?? null;
            $isPlaceholder = $validatedData['is_placeholder'];
          
            $isAdmin = $this->isAdmin;
         
            //process the request and update the challenge matrix and extra data using transaction
            $processResult = $this->challengeService->processRequest($validatedData, $brokerId, $isPlaceholder, $isAdmin,$zoneId);

            $responseData=$this->challengeService->getChallengeData($processResult['challenge_id'], $brokerId, $isPlaceholder, $zoneId);
            $responseData['category_id']=$validatedData['category_id'];
            $responseData['step_id']=$validatedData['step_id'];
            $responseData['amount_id']=$validatedData['amount_id']??null;
            $resposeData['broker_id']=$brokerId;
            $resposeData['zone_id']=$zoneId;
            $resposeData['is_placeholder']=$isPlaceholder;
            $resposeData['is_admin']=$isAdmin;
           
           
            return response()->json([
                'success' => true,
                'message' => 'Challenge matrix created successfully',
                'data' => $responseData
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create challenge matrix',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    
}
