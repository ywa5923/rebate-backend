<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Brokers\Services\ContestService;
use Modules\Brokers\Models\Contest;
use Modules\Brokers\Transformers\ContestResource;

class ContestController extends Controller
{
    protected ContestService $contestService;

    public function __construct(ContestService $contestService)
    {
        $this->contestService = $contestService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contests",
     *     tags={"Contest"},
     *     summary="Get all contests",
     *     @OA\Parameter(
     *         name="broker_id",
     *         in="query",
     *         description="Filter by broker ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="contest_type",
     *         in="query",
     *         description="Filter by contest type",
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
     *         name="current_date",
     *         in="query",
     *         description="Filter by current date (active contests)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="has_capacity",
     *         in="query",
     *         description="Filter by participant capacity",
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Contest")),
     *             @OA\Property(property="pagination", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="total", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request,int $broker_id)
    {
        try {
            $result = $this->contestService->getContests($request,$broker_id);
            
            // Transform the data collection
            $result['data'] = ContestResource::collection($result['data']);
            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve contests',
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
    public function store(Request $request): RedirectResponse
    {
        // Implementation for storing contest
        return redirect()->back();
    }

    /**
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
        // Implementation for updating contest
        return redirect()->back();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/contests/{id}",
     *     tags={"Contest"},
     *     summary="Delete contest",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contest ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="broker_id",
     *         in="query",
     *         description="Broker ID for authorization",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contest deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contest deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contest not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        // Check if broker ID is provided for authorization
        $broker_id = $request->broker_id;
        if ($broker_id == null) {
            throw new \Exception('Broker ID is required as a search parameter');
        }
      
        try {
            $this->contestService->deleteContest($id, $broker_id);

            return response()->json([
                'success' => true,
                'message' => 'Contest deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contest',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
