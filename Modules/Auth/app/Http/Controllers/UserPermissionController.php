<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\UserPermissionService;
use Modules\Auth\Http\Requests\StoreUserPermissionRequest;
use Modules\Auth\Http\Requests\UpdateUserPermissionRequest;
use Modules\Auth\Http\Requests\UserPermissionListRequest;
use Modules\Auth\Transformers\UserPermissionResource;
use App\Utilities\ModelHelper;
class UserPermissionController extends Controller
{
    protected UserPermissionService $userPermissionService;

    public function __construct(UserPermissionService $userPermissionService)
    {
        $this->userPermissionService = $userPermissionService;
    }

    /**
     * Get paginated list of user permissions with filters
     * 
     * @param UserPermissionListRequest $request
     * @return JsonResponse
     */
    public function index(UserPermissionListRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $perPage = $validated['per_page'] ?? 15;
            $orderBy = $validated['order_by'] ?? 'id';
            $orderDirection = $validated['order_direction'] ?? 'asc';

            // Collect filters
            $filters = [
                'subject_type' => $validated['subject_type'] ?? null,
                'subject_id' => $validated['subject_id'] ?? null,
                'permission_type' => $validated['permission_type'] ?? null,
                'resource_id' => $validated['resource_id'] ?? null,
                'resource_value' => $validated['resource_value'] ?? null,
                'action' => $validated['action'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'is_active' => $validated['is_active'] ?? null,
            ];

            $permissions = $this->userPermissionService->getAll($filters, $orderBy, $orderDirection, $perPage);

            return response()->json([
                'success' => true,
                'data' => UserPermissionResource::collection($permissions->items()),
                'meta' => [
                    'current_page' => $permissions->currentPage(),
                    'last_page' => $permissions->lastPage(),
                    'per_page' => $permissions->perPage(),
                    'total' => $permissions->total(),
                    'from' => $permissions->firstItem(),
                    'to' => $permissions->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user permissions list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single user permission by ID
     * 
     * @param int $user_permission
     * @return JsonResponse
     */
    public function show(int $user_permission): JsonResponse
    {
        try {
            $permission = $this->userPermissionService->getById($user_permission);

            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'User permission not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new UserPermissionResource($permission),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user permission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new user permission
     * 
     * @param StoreUserPermissionRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserPermissionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $subject_type = $data['subject_type'];
            //subject type is a PlatformUser or BrokerTeamUser defined in Auth module
            $modelClass = ModelHelper::getModelClassFromSlug($subject_type,'Modules\\Auth\\Models\\');
           
            if (!$modelClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subject type',
                ], 400);
            }
          
            $data['subject_type'] = $modelClass;    
            $permission = $this->userPermissionService->createPermission($data);

            return response()->json([
                'success' => true,
                'message' => 'User permission created successfully',
                'data' => new UserPermissionResource($permission),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user permission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a user permission
     * 
     * @param UpdateUserPermissionRequest $request
     * @param int $user_permission
     * @return JsonResponse
     */
    public function update(UpdateUserPermissionRequest $request, int $user_permission): JsonResponse
    {
        try {
            $data = $request->validated();
            $subject_type = $data['subject_type'];
            //subject type is a PlatformUser or BrokerTeamUser defined in Auth module
            $modelClass = ModelHelper::getModelClassFromSlug($subject_type,'Modules\\Auth\\Models\\');
            if (!$modelClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subject type',
                ], 400);
            }
            
            $data['subject_type'] = $modelClass;    
            $permission = $this->userPermissionService->updatePermission($user_permission, $data);

            return response()->json([
                'success' => true,
                'message' => 'User permission updated successfully',
                'data' => new UserPermissionResource($permission),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user permission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user permission
     * 
     * @param int $user_permission
     * @return JsonResponse
     */
    public function destroy(int $user_permission): JsonResponse
    {
        try {
            $deleted = $this->userPermissionService->deletePermission($user_permission);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'User permission not found or could not be deleted',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User permission deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user permission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status of a user permission
     * 
     * @param int $user_permission
     * @return JsonResponse
     */
    public function toggleActiveStatus(int $user_permission): JsonResponse
    {
        try {
            $permission = $this->userPermissionService->toggleActiveStatus($user_permission);

            return response()->json([
                'success' => true,
                'message' => 'Permission active status updated successfully',
                'data' => new UserPermissionResource($permission),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle permission status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

