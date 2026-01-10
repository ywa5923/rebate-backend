<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;

use Modules\Brokers\Services\ChallengeCategoryService;
use Modules\Brokers\Transformers\ChallengeCategoryResource;

use Modules\Brokers\Models\Challenge;

use Modules\Brokers\Services\ChallengeService;

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
        $responseData= [];

        $matrix = $chId?$this->challengeService->getChallengeMatrixData($chId):null;
        $discount = $chId?$this->challengeService->findDiscountByChallengeId($chId, $brokerId):null;
        $affiliateLink = $chId?$this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $chId, $brokerId, $isPlaceholder,  $zoneId):null;
        $affiliateMasterLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, $isPlaceholder, $zoneId);
        $responseData = [
            'challenge_id' => $chId,
            'matrix' => $matrix,
            'evaluation_cost_discount' => $discount,
            'affiliate_link' => $affiliateLink,
            'affiliate_master_link' => $affiliateMasterLink
        ];
        if(!$isPlaceholder){
            //if not placeholder mode, add the placeholder data for matrix and matrix extradata:affiliate link, affiliate master link, evaluation cost discount
            $this->challengeService->addPlaceholderData($responseData, $categoryId, $stepId, $zoneId);
        }
        
        return response()->json([
            'success' => true,
            'data' => array_filter($responseData, fn($v) => $v !== null)
        ]);
        // if ($validatedData['is_placeholder']) {

            //     $responseData= [
            //         'challenge_id' => $chId,
            //         'matrix' => $chId?$this->challengeService->getChallengeMatrixData($chId):null,
            //         'evaluation_cost_discount' => $chId?$this->challengeService->findDiscountByChallengeId($chId, $brokerId):null,
            //         'affiliate_link' => $chId?$this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $chId, $brokerId, true,  $zoneId):null,
            //         'affiliate_master_link' => $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)
            //     ];
               
            //     //return the placeholder data for matrix and matrix extradata:affiliate link, affiliate master link, evaluation cost discount
            //     //return response()->json($this->challengeService->handlePlaceholderRequest($validatedData));
            // }else{
            //     $responseData = [
            //         'challenge_id' => $chId,
            //         'matrix' => $chId?$this->challengeService->getChallengeMatrixData($chId):null,
            //         'evaluation_cost_discount' => $chId?$this->challengeService->findDiscountByChallengeId($chId, $brokerId, $zoneId):null,
            //         'affiliate_link' => $chId?$this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $chId, $brokerId, false, $zoneId):null,
            //         'affiliate_master_link' => $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, false, $zoneId)
            //     ];   
            //     $this->challengeService->addPlaceholderData($responseData, $categoryId, $stepId, $zoneId);

            // }
            // return response()->json([
            //     'success' => true,
            //     'data' => array_filter($responseData, fn($v) => $v !== null)
            // ]);
            //return the regular challenge data for challenge matrix and matrix extradata:affiliate link, affiliate master link, evaluation cost discount
            //return response()->json($this->challengeService->handleRegularChallengeRequest($validatedData));

        } catch (\Illuminate\Validation\ValidationException $e) {
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
        // $categories = ChallengeCategory::with('steps','amounts')->get();
        // return response()->json($categories);
        try {
            $result = $this->challengeCategoryService->getChallengeCategories($request);

            // Transform the data collection
            $result['data'] = ChallengeCategoryResource::collection($result['data']);
            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve challenge categories',
                'error' => $e->getMessage()
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
            $result = $this->challengeService->processRequest($validatedData, $brokerId, $isPlaceholder, $isAdmin,$zoneId);

            return response()->json([
                'success' => true,
                'message' => 'Challenge matrix created successfully',
                'data' => [
                    'challenge_id' => $result['challenge_id']
                ]
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
