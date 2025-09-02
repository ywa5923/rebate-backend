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
                'is_placeholder' => 'nullable|boolean'
            ]);

            $brokerId = 1; // Default broker ID

            $isPalceholder=$validatedData['is_placeholder'];
            if($validatedData['is_placeholder']==false || $validatedData['is_placeholder']==null){
            //First find the challenge that is not placeholder 
            $challenge = $this->challengeService->findChallengeByParams(
                false,
                $validatedData['category_id'],
                $validatedData['step_id'],
                $validatedData['amount_id'] ?? null,
                $brokerId
            );

            //if not found then find the challenge that is placeholder 
            if (!$challenge) {

                //find the challenge that is placeholder
                $challenge = $this->challengeService->challengeExist(
                   true,
                    $validatedData['category_id'],
                    $validatedData['step_id'],
                    $validatedData['amount_id'] ?? null,
                    $brokerId,
                );
                if (!$challenge) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Challenge not found'
                    ], 404);
                }else{
                    $isPalceholder=true;
                }
            }

            }else if($validatedData['is_placeholder']==true){
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

            return response()->json([
                'success' => true,
                'data' => [
                    'challenge_id' => $challenge->id,
                    'challenge_category_id' => $challenge->challenge_category_id,
                    'challenge_step_id' => $challenge->challenge_step_id,
                    'challenge_amount_id' => $challenge->challenge_amount_id,
                    'is_placeholder' => $challenge->is_placeholder,
                    'matrix' => $matrix
                ]
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
        $brokerId = 1;
        
        try {
            // Validate the request
            $validatedData = $request->validate([
                'category_id' => 'required|integer|exists:challenge_categories,id',
                'step_id' => 'required|integer|exists:challenge_steps,id', 
                'step_slug' => 'nullable|string',
                'amount_id' => 'nullable|integer|exists:challenge_amounts,id',
                'is_placeholder' => 'nullable|boolean',
                'matrix' => 'required|array'
            ]);

            // Use service to store challenge
            $result = $this->challengeService->storeChallenge($validatedData, $brokerId);

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create challenge matrix',
                'error' => $e->getMessage()
            ], 500);
        }
    }
     /**
     * Save matrix data to matrix_values table
  
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('brokers::show');
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
