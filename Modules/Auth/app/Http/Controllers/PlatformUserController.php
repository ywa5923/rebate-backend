<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\PlatformUserService;
use Modules\Auth\Services\UserPermissionService;
use Modules\Auth\Http\Requests\StorePlatformUserRequest;
use Modules\Auth\Http\Requests\UpdatePlatformUserRequest;
use Modules\Auth\Http\Requests\PlatformUserListRequest;
use Modules\Auth\Transformers\PlatformUserResource;
use Modules\Auth\Tables\PlatformUsersTableConfig;
use Modules\Auth\Forms\PlatformUserForm;
use Modules\Auth\Models\BrokerTeamUser;
class PlatformUserController extends Controller
{
    //protected PlatformUserService $platformUserService;
   // protected UserPermissionService $permissionService;

    public function __construct(
        protected PlatformUserService $platformUserService,
        protected UserPermissionService $permissionService,
        protected PlatformUsersTableConfig $tableConfig,
        protected PlatformUserForm $formConfig,
    )
    {
       
    }

    /**
     * Get paginated list of platform users with filters
     * 
     * @param PlatformUserListRequest $request
     * @return JsonResponse
     */
    public function index(PlatformUserListRequest $request): JsonResponse
    {
        try {
           
            $filters = $request->getFilters();
            $orderBy = $request->getOrderBy();
           // dd($filters, $orderBy);
            
            $orderDirection = $request->getOrderDirection();
            $perPage = $request->getPerPage();

            $users = $this->platformUserService->getAll($filters, $orderBy, $orderDirection, $perPage);

            return response()->json([
                'success' => true,
                'data' => PlatformUserResource::collection($users->items()),
                'table_columns_config' => $this->tableConfig->columns(),
                'filters_config' => $this->tableConfig->filters(),
                'form_config' => $this->formConfig->getFormData(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get platform users list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get form config for platform users
     * 
     * @return JsonResponse
     */
    public function getFormConfig(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->formConfig->getFormData(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get form config',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single platform user by ID
     * 
     * @param int $platform_user
     * @return JsonResponse
     */
    public function show(int $platform_user): JsonResponse
    {
        try {
            $user = $this->platformUserService->getById($platform_user);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Platform user not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new PlatformUserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get platform user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new platform user
     * 
     * @param StorePlatformUserRequest $request
     * @return JsonResponse
     */
    public function store(StorePlatformUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
                
            // Hash password if provided
            // if (isset($data['password']) && !empty($data['password'])) {
            //     $data['password'] = bcrypt($data['password']);
            // } else {
            //     unset($data['password']);
            // }

            //check if this user is not registered as a broker team user
            $brokerTeamUser = BrokerTeamUser::where('email', $data['email'])->first();
            if ($brokerTeamUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already registered as a broker team user',
                ], 400);
            }
             //check if this user is not registered as a platform user is made in request's rule
           
            $user = $this->platformUserService->create($data);

            //send email to registered user
            //Mail::to($user->email)->send(new PlatformUserCreatedMail($user));

            return response()->json([
                'success' => true,
                'message' => 'Platform user created successfully',
                'data' => new PlatformUserResource($user),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create platform user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a platform user
     * 
     * @param UpdatePlatformUserRequest $request
     * @param int $platform_user
     * @return JsonResponse
     */
    public function update(UpdatePlatformUserRequest $request, int $platform_user): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['id'] = $platform_user;

            // Hash password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }

            $user = $this->platformUserService->update($data);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Platform user not found or could not be updated',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Platform user updated successfully',
                'data' => new PlatformUserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update platform user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a platform user
     * 
     * @param int $platform_user
     * @return JsonResponse
     */
    public function destroy(int $platform_user): JsonResponse
    {
        try {
            $deleted = $this->platformUserService->delete($platform_user);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Platform user not found or could not be deleted',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Platform user deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete platform user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status of a platform user
     * 
     * @param int $platform_user
     * @return JsonResponse
     */
    public function toggleActiveStatus(int $platform_user): JsonResponse
    {
        try {
            $user = $this->platformUserService->toggleActiveStatus($platform_user);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Platform user not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Platform user active status updated successfully',
                'data' => new PlatformUserResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle platform user status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
