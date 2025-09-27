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

    public function __construct(ChallengeCategoryService $challengeCategoryService, ChallengeService $challengeService)
    {
        $this->challengeCategoryService = $challengeCategoryService;
        $this->challengeService = $challengeService;
    }


    public function show(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'category_id' => 'required|integer|exists:challenge_categories,id',
                'step_id' => 'required|integer|exists:challenge_steps,id',
                'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
                'is_placeholder' => 'nullable|boolean',
                'broker_id' => 'nullable|integer|exists:brokers,id',
                'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
            ]);

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


            $responseArray = [
                'challenge_category_id' => $placeholderChallenge->challenge_category_id,
                'challenge_step_id' => $placeholderChallenge->challenge_step_id,
                'challenge_amount_id' => $placeholderChallenge->challenge_amount_id,
                'is_placeholder' =>  $isPlaceholder,
            ];

            if ($isPlaceholder) {
                //this code is executed when the admin is in the placehoder page
                //and he wants to view only the placehoder data for challenge matrix and
                //discount, affiliate link and affiliate master link

                //if placeholder challenge is found then return the placehoder data
                //placeholder challenge is an entry in the challenge table that has is_placeholder true
                //and amount_id is null
                //a challenge  has a different id for every combination of category, step and ammount
                //we use the challenge id to get the matrix, discount, affiliate link and affiliate master link
                //for every combination of category, step and ammount

                if ($placeholderChallenge?->id) {
                    //get the placehoder data for challenge matrix, discount, affiliate link and affiliate master link
                    $responseArray = array_merge($responseArray, [
                        'challenge_id' => $placeholderChallenge->id,
                        'matrix' => $this->challengeService->getChallengeMatrixData($placeholderChallenge->id),
                        'evaluation_cost_discount' => $this->challengeService->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId),
                        'affiliate_link' => $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId),
                        'affiliate_master_link' => $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)
                    ]);

                    return response()->json([
                        'success' => true,
                        'data' => $responseArray
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Placeholder challenge not found'
                    ], 404);
                }
            }

            //this code is executed when the user or admin is in the challenge matrix page
            //and he wants to view and edit the challenge matrix data
            //if challenge is found then return the challenge data


            // First, try to find the specific challenges  non-placeholder and placeholder
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
                $affiliateMasterLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, false, $zoneId);

                $responseArray = array_merge($responseArray, [
                    'challenge_id' => $challenge->id,
                    'matrix' => $matrix,
                    'evaluation_cost_discount' => $discount,
                    'affiliate_link' => $affiliateLink,
                    'affiliate_master_link' => $affiliateMasterLink
                ]);


                // Check if matrix has empty cells - if so, get placeholder data
                if ($this->hasEmptyMatrixCells($matrix) && $placeholderChallenge?->id) {
                    $responseArray['matrix_placeholders_array'] = $this->getMatrixPlaceholderArray($placeholderChallenge->id, $challenge->id);
                }

                //dd($affiliateLink, $affiliateMasterLink, $discount);
                //if discount and affiliate links are null then get the placeholder data
                if ($placeholderChallenge?->id) {

                    is_null($discount) && $responseArray['evaluation_cost_discount_placeholder'] = $this->challengeService->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId)?->broker_value;


                    is_null($affiliateLink) && $responseArray['affiliate_link_placeholder'] =
                        $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, true, $zoneId)?->url;

                    is_null($affiliateMasterLink) && $responseArray['affiliate_master_link_placeholder'] =
                        $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)?->url;
                }

            } else {

                //if challenge is not found and we are not in the placeholder page then return the placeholder data
                //to be displayed in the challenge matrix page if a cell is empty
                if ($placeholderChallenge?->id) {
                    $responseArray = array_merge($responseArray, [
                        'challenge_id' => $placeholderChallenge->id,
                        'matrix' => null,
                        'matrix_placeholders_array' => $this->getMatrixPlaceholderArray($placeholderChallenge->id, null),
                        'evaluation_cost_discount_placeholder' =>
                        $this->challengeService->findDiscountByChallengeId($placeholderChallenge->id, $brokerId, $zoneId)?->broker_value,

                        'affiliate_link_placeholder' =>
                        $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $placeholderChallenge->id, $brokerId, false, $zoneId)?->url,

                        'affiliate_master_link_placeholder' =>
                        $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, true, $zoneId)?->url,

                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $responseArray
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve challenge data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function hasEmptyMatrixCells($matrix): bool
    {
        foreach ($matrix as $row) {
            foreach ($row as $cell) {
                if (empty($cell['value']['text']) || (is_array($cell['value']['text']) && empty(array_filter($cell['value']['text'])))) {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Check if matrix has empty cells
     */
    private function getPlaceholdersCellsArray(array $placeholderMatrix, ?array $matrix = null): array
    {
        $placeholders = [];
        if ($matrix) {
            foreach ($matrix as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    if (empty($cell['value']['text']) || (is_array($cell['value']['text']) && empty(array_filter($cell['value']['text'])))) {
                        // $placeholders[] = $cell;
                        $placeholders[$cell['rowHeader'] . '-' . $cell['colHeader']] = $placeholderMatrix[$rowIndex][$colIndex]['value']['text'];
                    }
                }
            }
        } else {
            foreach ($placeholderMatrix as $rowIndex => $row) {
                foreach ($row as $colIndex => $cell) {
                    $placeholders[$cell['rowHeader'] . '-' . $cell['colHeader']] = $cell['value']['text'];
                }
            }
        }
        return $placeholders;
    }

    /**
     * Get placeholder data (lazy loading)
     */
    private function getMatrixPlaceholderArray($placeholderChallengeId, ?int $challengeId = null): array
    {
        $placeholderMatrix = $this->challengeService->getChallengeMatrixData($placeholderChallengeId);
        $challengeMatrix = ($challengeId) ? $this->challengeService->getChallengeMatrixData($challengeId) : null;
        return $this->getPlaceholdersCellsArray($placeholderMatrix, $challengeMatrix);
    }
    /**
     * 
  
     * Show the specified resource.
     */
    public function show2(Request $request): JsonResponse

    {
        try {
            $validatedData = $request->validate([
                'category_id' => 'required|integer|exists:challenge_categories,id',
                'step_id' => 'required|integer|exists:challenge_steps,id',
                'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
                'is_placeholder' => 'nullable|boolean',
                'broker_id' => 'nullable|integer|exists:brokers,id',
                'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
            ]);
            $brokerId = $validatedData['broker_id']; // Default broker ID
            $zoneId = $validatedData['zone_id'] ?? null;
            $isPalceholder = $validatedData['is_placeholder'];
            $responseArray = [];

            $challengePlaceholder = $this->challengeService->findChallengeByParams(
                true,
                $validatedData['category_id'],
                $validatedData['step_id'],
                null,
                $brokerId
            );

            if ($challengePlaceholder?->id) {
                $placeholderMatrix = $this->challengeService->getChallengeMatrixData($challengePlaceholder->id);
                $discountPlaceholder = $this->challengeService->findDiscountByChallengeId($challengePlaceholder->id, $brokerId, $zoneId);
                $affiliateLinkPlaceholder = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $challengePlaceholder->id, $brokerId, $zoneId);
                $affiliateMasterLinkPlaceholder = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, $zoneId);

                $resonseArray['matrix_placeholders'] = $placeholderMatrix;
                $resonseArray['discount_placeholder'] = $discountPlaceholder;
                $resonseArray['affiliate_link_placeholder'] = $affiliateLinkPlaceholder;
                $resonseArray['affiliate_master_link_placeholder'] = $affiliateMasterLinkPlaceholder;

                if ($isPalceholder) {
                    //return only placehoder data to view and edit in placehoder page
                    return response()->json([
                        'success' => true,
                        'data' => $resonseArray
                    ]);
                }
            }


            $challenge = $this->challengeService->findChallengeByParams(
                false,
                $validatedData['category_id'],
                $validatedData['step_id'],
                $validatedData['amount_id'],
                $brokerId
            );

            if ($challenge?->id) {
                $matrix = $this->challengeService->getChallengeMatrixData($challenge->id);
                $discount = $this->challengeService->findDiscountByChallengeId($challenge->id, $brokerId, $zoneId);
                $affiliateLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $challenge->id, $brokerId, $zoneId);
                $affiliateMasterLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, $zoneId);
            }


            $resonseArray = [
                'challenge_id' => $challenge->id,
                'challenge_category_id' => $challenge->challenge_category_id,
                'challenge_step_id' => $challenge->challenge_step_id,
                'challenge_amount_id' => $challenge->challenge_amount_id,
                'is_placeholder' => $challenge->is_placeholder,
                'matrix' => $matrix
            ];


            return response()->json([
                'success' => true,
                'data' => $resonseArray
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate the request parameters
            $validatedData = $request->validate([
                'category_id' => 'required|integer|exists:challenge_categories,id',
                'step_id' => 'required|integer|exists:challenge_steps,id',
                'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
                'is_placeholder' => 'nullable|boolean',
                'broker_id' => 'nullable|integer|exists:brokers,id',
                'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
            ]);

            $brokerId = $validatedData['broker_id']; // Default broker ID
            $zoneId = $validatedData['zone_id'] ?? null;
            // $brokerId=1;
            $isPalceholder = $validatedData['is_placeholder'];
            if ($validatedData['is_placeholder'] == false || $validatedData['is_placeholder'] == null || $validatedData['is_placeholder'] == "0" || $validatedData['is_placeholder'] == 0) {
                //First find the challenge that is not placeholder 
                $challenge = $this->challengeService->findChallengeByParams(
                    false,
                    $validatedData['category_id'],
                    $validatedData['step_id'],
                    $validatedData['amount_id'],
                    $brokerId
                );

                // dd($challenge);

                //if not found then find the challenge that is placeholder 
                if (!$challenge) {

                    //find the challenge that is placeholder
                    //placeholder challenges entries have amount_id null,they differ only by step_id and category_id
                    $challenge = $this->challengeService->challengeExist(
                        true,
                        $validatedData['category_id'],
                        $validatedData['step_id'],
                        null,
                        $brokerId,
                    );
                    if (!$challenge) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Challenge not found'
                        ], 404);
                    } else {
                        $isPalceholder = true;
                    }
                }
            } else if ($validatedData['is_placeholder'] == true || $validatedData['is_placeholder'] == "1" || $validatedData['is_placeholder'] == 1) {
                //the client  get only the matrix data for placeholder challenge
                $challenge = $this->challengeService->findChallengeByParams(
                    true,
                    $validatedData['category_id'],
                    $validatedData['step_id'],
                    $validatedData['amount_id'] ?? null,
                    $brokerId
                );
            }


            // Get matrix data in the required format
            //TODO:add translation and zone params
            $matrix = $this->challengeService->getChallengeMatrixData($challenge->id);

            $resonseArray = [
                'challenge_id' => $challenge->id,
                'challenge_category_id' => $challenge->challenge_category_id,
                'challenge_step_id' => $challenge->challenge_step_id,
                'challenge_amount_id' => $challenge->challenge_amount_id,
                'is_placeholder' => $challenge->is_placeholder,
                'matrix' => $matrix
            ];



            //for challenge matrix that is not placeholder we need to add the affiliate link and evaluation cost discount
            if (!$isPalceholder) {
                $affiliateLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, $challenge->id, $brokerId, false, $zoneId);
                $affiliateMasterLink = $this->challengeService->findUrlByUrlableTypeAndId(Challenge::class, null, $brokerId, false, $zoneId);
                $discountValue = $this->challengeService->findDiscountByChallengeId($challenge->id, $brokerId, $zoneId);


                $affiliateLink && $resonseArray['affiliate_link'] = AffiliateLinkResource::make($affiliateLink);
                $affiliateMasterLink && $resonseArray['affiliate_master_link'] = AffiliateLinkResource::make($affiliateMasterLink);
                $discountValue && $resonseArray['evaluation_cost_discount'] = CostDiscountResource::make($discountValue);
            }

            return response()->json([
                'success' => true,
                'data' => $resonseArray
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve challenge matrix data',
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
            // Validate the request
            $validatedData = $request->validate([
                'category_id' => 'required|integer|exists:challenge_categories,id',
                'step_id' => 'required|integer|exists:challenge_steps,id',
                'step_slug' => 'nullable|string',
                'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
                'is_placeholder' => 'nullable|boolean',
                'matrix' => 'required|array',
                'broker_id' => 'nullable|integer|exists:brokers,id',
                'zone_id' => 'sometimes|nullable|integer|exists:zones,id',
                'is_admin' => 'sometimes|nullable|boolean',
                'evaluation_cost_discount' => 'sometimes|nullable|string',
                'affiliate_link' => 'sometimes|nullable|string',
                'affiliate_master_link' => 'sometimes|nullable|string',
            ]);

            $brokerId = $validatedData['broker_id'];
            $zoneId = $validatedData['zone_id'] ?? null;
            $isAdmin = $validatedData['is_admin'] ?? null;



            $isAdmin = true;
            // Use service to store challenge
            // $challengeId = 
            //    $challenge = $this->challengeService->findChallengeByParams(
            //     false,
            //     $validatedData['category_id'],
            //     $validatedData['step_id'],
            //     $validatedData['amount_id'],
            //     $brokerId
            // );
            // if($challenge){

            //     $previousChalengeMatrix = $this->challengeService->getChallengeMatrixData($challenge->id, $zoneId);
            //     $newMAtrix=$this->challengeService->setPreviousValueInMatrixData($previousChalengeMatrix, $validatedData['matrix']);

            // }

            $result = $this->challengeService->storeChallengeMatrix($validatedData, $brokerId, $zoneId, $isAdmin);

            return response()->json([
                'success' => true,
                'message' => 'Challenge matrix created successfully',
                'data' => [
                    'challenge_id' => $result['challenge_id']
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
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
