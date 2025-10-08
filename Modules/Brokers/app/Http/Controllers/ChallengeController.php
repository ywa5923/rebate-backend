<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Models\ChallengeCategory;
use Modules\Brokers\Services\ChallengeCategoryService;
use Modules\Brokers\Transformers\ChallengeCategoryResource;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\Challenge;
use Modules\Brokers\Models\Matrix;
use Modules\Brokers\Models\MatrixValue;
use Modules\Brokers\Models\MatrixDimension;
use Modules\Brokers\Models\MatrixHeader;
use Modules\Brokers\Models\ChallengeMatrixValue;
use Modules\Brokers\Services\ChallengeService;
use Modules\Brokers\Transformers\AffiliateLinkResource;
use Modules\Brokers\Transformers\CostDiscountResource;

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
     * =========== DEPRECATED FUNCTION =================
     * Show the challenge data
     * @param Request $request
     * @return JsonResponse
     */
    public function showOld(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->challengeService->validateGetRequestData($request);
            
            $brokerId = $validatedData['broker_id'] ?? 1;
            $zoneId = $validatedData['zone_id'] ?? null;
            $isPlaceholder = $validatedData['is_placeholder'];

            $placeholderChallenge = $this->challengeService->findChallengeByParams(
                true,
                $validatedData['category_id'],
                $validatedData['step_id'],
                null,
                $brokerId
            );
           
            $responseArray =   [];

            if ($isPlaceholder && $placeholderChallenge?->id) {
                //this code is executed when the admin is in the placehoder page
                //and he wants to view only the placehoder data for challenge matrix and
                //discount, affiliate link and affiliate master link

                //if placeholder challenge is found then return the placehoder data
                //placeholder challenge is an entry in the challenge table that has is_placeholder true
                //and amount_id is null
                //a challenge  has a different id for every combination of category, step and ammount
                //we use the challenge id to get the matrix, discount, affiliate link and affiliate master link
                //for every combination of category, step and ammount

                //in placeholder mode the data is returned same as for the challenges matrix,but has a different $challengeId

                   // dd($placeholderChallenge->id);
                    //get the placehoder data for challenge matrix, discount, affiliate link and affiliate master link
                    $responseArray =   array_merge($responseArray, [
                        'challenge_id' => $placeholderChallenge->id,
                        'matrix' => $this->challengeService->getChallengeMatrixData($placeholderChallenge->id),
                        'evaluation_cost_discount' => $this->challengeService->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId),
                        'affiliate_link' => $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId),
                        //get master affiliate link that is placeholder,so the 4th parameter is true
                        'affiliate_master_link' => $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)
                    ]);

                    return response()->json([
                        'success' => true,
                        'data' => $responseArray
                    ]);
                
            }elseif($isPlaceholder)
            {
                //if the admin is in the placeholder page and the placeholder challenge is not found
                //then return only the affiliate master link placeholder which is the same for all challenges combinations
                $placeholderMasterLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId);
                $responseArray['affiliate_master_link'] = $placeholderMasterLink;

                return response()->json([
                    'success' => true,
                    'message' => 'Placeholder challenge not found,return only the affiliate master link placeholder', 
                    'data' => $responseArray
                ]);
            }

            //this code is executed when the user or admin is in the challenge matrix page
            //and he wants to view and edit the challenge matrix data
            //if challenge is found then return the challenge data


            // First, try to find the specific challenge  that is not placeholder
            $challenge = $this->challengeService->findChallengeByParams(
                false,
                $validatedData['category_id'],
                $validatedData['step_id'],
                $validatedData['amount_id'],
                $brokerId
            );
           

            if ($challenge?->id) {
                // Get the main challenge data
                $matrix = $this->challengeService->getChallengeMatrixData($challenge->id);
                $discount = $this->challengeService->findDiscountByChallengeId($challenge->id, $brokerId, $zoneId);
                $affiliateLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $challenge->id, $brokerId, false, $zoneId);
                 //get master affiliate link that is not placeholder,so the 4th parameter is false
                $affiliateMasterLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, false, $zoneId);
              
                $responseArray = array_merge($responseArray, [
                    'challenge_id' => $challenge->id,
                    'matrix' => $matrix,
                    'evaluation_cost_discount' => $discount,
                    'affiliate_link' => $affiliateLink,
                    'affiliate_master_link' => $affiliateMasterLink
                ]);


                // Check if matrix has empty cells - if so, get placeholder array for matrix cells
                if ($this->challengeService->hasEmptyMatrixCells($matrix) && $placeholderChallenge?->id) {
                    $responseArray['matrix_placeholders_array'] = $this->challengeService->getMatrixPlaceholderArray($placeholderChallenge->id, $challenge->id);
                }

                
                //if discount and affiliate links are null then get the placeholder data
                if ($placeholderChallenge?->id) {

                    is_null($discount) && $responseArray['evaluation_cost_discount_placeholder'] = $this->challengeService->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId)?->value;


                    is_null($affiliateLink) && $responseArray['affiliate_link_placeholder'] =
                        $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId)?->url;

                    is_null($affiliateMasterLink) && $responseArray['affiliate_master_link_placeholder'] =
                        $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)?->url;
                }

            } else {

                $affiliateMasterLinkObiect = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, false, $zoneId);
                //if challenge is not found and we are not in the placeholder page then return only the placeholder data
                //to be displayed in the challenge matrix page if a cell is empty
               $affiliateMasterLinkPlaceholder = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)?->url;
               $responseArray['affiliate_master_link'] = $affiliateMasterLinkObiect;

               //if the affiliate master link is null then return the placeholder data for it
               is_null($affiliateMasterLinkObiect) && $responseArray['affiliate_master_link_placeholder'] = $affiliateMasterLinkPlaceholder;
                
               if ($placeholderChallenge?->id) {
                    $responseArray = array_merge($responseArray, [
                        'challenge_id' => $placeholderChallenge->id,
                        'matrix' => null,
                        'matrix_placeholders_array' => $this->challengeService->getMatrixPlaceholderArray($placeholderChallenge->id, null),
                        'evaluation_cost_discount_placeholder' =>
                        $this->challengeService->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId)?->value,

                        'affiliate_link_placeholder' =>
                        $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId)?->url,

                        'affiliate_master_link_placeholder' =>
                        $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)?->url,

                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $responseArray
            ]);
        }
       catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve challenge data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refactored show method using helper methods from ChallengeService
     */
    public function show(Request $request): JsonResponse
    {
        
        try {
            $validatedData = $this->challengeService->validateGetRequestData($request);

            $brokerId = $validatedData['broker_id'] ?? 1;
            $zoneId = $validatedData['zone_id'] ?? null;
            $isPlaceholder = $validatedData['is_placeholder'];

            if ($isPlaceholder) {
                //return the placeholder data for matrix and matrix extradata:affiliate link, affiliate master link, evaluation cost discount
                return response()->json($this->challengeService->handlePlaceholderRequest($validatedData, $brokerId, $zoneId));
            }

            //return the regular challenge data for challenge matrix and matrix extradata:affiliate link, affiliate master link, evaluation cost discount
            return response()->json($this->challengeService->handleRegularChallengeRequest($validatedData, $brokerId, $zoneId));

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
     * =========== DEPRECATED FUNCTION =================
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Deprecated function',
            'error' => 'This function is deprecated'
        ], 400);
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('brokers::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try { 
            $validatedData = $this->challengeService->validatePostRequestData($request);
            $brokerId = $validatedData['broker_id'];
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
        // Implementation for updating challenge category
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Implementation for deleting challenge category
    }
}
