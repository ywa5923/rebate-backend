<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Brokers\Services\ZoneService;
use Modules\Brokers\Http\Requests\StoreZoneRequest;
use Modules\Brokers\Http\Requests\UpdateZoneRequest;
use Modules\Brokers\Http\Requests\ZoneListRequest;
use Modules\Brokers\Transformers\ZoneResource;

class ZoneController extends Controller
{
    public function __construct(
        protected ZoneService $zoneService
    ) {
    }

    /**
     * Get paginated list of zones with filters
     * 
     * @param ZoneListRequest $request
     * @return JsonResponse
     */
    public function index(ZoneListRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $perPage = $validated['per_page'] ?? 15;
            $orderBy = $validated['order_by'] ?? 'id';
            $orderDirection = $validated['order_direction'] ?? 'asc';
            
            // Collect and sanitize filters
            $filters = [
                'name' => !empty($validated['name']) ? $this->sanitizeLikeInput($validated['name']) : null,
                'zone_code' => !empty($validated['zone_code']) ? $this->sanitizeLikeInput($validated['zone_code']) : null,
                'description' => !empty($validated['description']) ? $this->sanitizeLikeInput($validated['description']) : null,
            ];
            
            $zones = $this->zoneService->getZoneList($perPage, $orderBy, $orderDirection, $filters);
            
            return response()->json([
                'success' => true,
                'data' => ZoneResource::collection($zones->items()),
                'pagination' => [
                    'current_page' => $zones->currentPage(),
                    'last_page' => $zones->lastPage(),
                    'per_page' => $zones->perPage(),
                    'total' => $zones->total(),
                    'from' => $zones->firstItem(),
                    'to' => $zones->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get zone list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single zone by ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $zone = $this->zoneService->getZoneById($id);
            
            return response()->json([
                'success' => true,
                'data' => new ZoneResource($zone),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Zone not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get zone',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new zone
     * 
     * @param StoreZoneRequest $request
     * @return JsonResponse
     */
    public function store(StoreZoneRequest $request): JsonResponse
    {
        try {
            $zone = $this->zoneService->createZone($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Zone created successfully',
                'data' => new ZoneResource($zone),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create zone',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing zone
     * 
     * @param UpdateZoneRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateZoneRequest $request, int $id): JsonResponse
    {
        try {
            $zone = $this->zoneService->updateZone($id, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Zone updated successfully',
                'data' => new ZoneResource($zone),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Zone not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update zone',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a zone
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->zoneService->deleteZone($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Zone deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Zone not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete zone',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get zone statistics (countries and brokers count)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function statistics(int $id): JsonResponse
    {
        try {
            $stats = $this->zoneService->getZoneStatistics($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'zone' => new ZoneResource($stats['zone']),
                    'countries_count' => $stats['countries_count'],
                    'brokers_count' => $stats['brokers_count'],
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Zone not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get zone statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sanitize input for LIKE queries by escaping special characters
     * 
     * @param string $input
     * @return string
     */
    private function sanitizeLikeInput(string $input): string
    {
        // Escape special LIKE characters: %, _, \
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $input);
    }
}

