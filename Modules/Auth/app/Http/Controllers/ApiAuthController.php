<?php

namespace Modules\Auth\Http\Controllers;

use Modules\Auth\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Modules\Auth\Models\MagicLink;
use Modules\Auth\Models\BrokerTeam;
use Modules\Auth\Models\BrokerTeamUser;
use Modules\Auth\Models\PlatformUser;
use Modules\Auth\Services\MagicLinkService;
use Modules\Auth\Services\BrokerTeamService;
use Modules\Auth\Services\UserPermissionService;
use Modules\Auth\Services\SuperAdminService;
use Modules\Auth\Mail\MagicLinkMail;
use Illuminate\Support\Facades\Log;
use Modules\Brokers\Models\OptionValue;
use Modules\Brokers\Models\BrokerOption;

class ApiAuthController extends Controller
{
    protected MagicLinkService $magicLinkService;
    protected BrokerTeamService $teamService;
    protected UserPermissionService $permissionService;
    protected SuperAdminService $superAdminService;

    public function __construct(
        MagicLinkService $magicLinkService, 
        BrokerTeamService $teamService,
        UserPermissionService $permissionService,
        SuperAdminService $superAdminService
    ) {
        $this->magicLinkService = $magicLinkService;
        $this->teamService = $teamService;
        $this->permissionService = $permissionService;
        $this->superAdminService = $superAdminService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        return response()->json([]);
    }

   
    public function login(Request $request)
    {
        //

        return response()->json([]);
    }

    public function logout(Request $request)
    {
        //

        return response()->json([]);
    }   
    
    /**
     * Register a new broker
     */
    public function registerBroker(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'broker_type_name' => 'required|string|exists:broker_types,name',
                'email' => [
                    'required',
                    'email',
                  
                   // Rule::unique('platform_users', 'email'),
                    Rule::unique('broker_team_users', 'email'),
                ],
                'trading_name' => 'required|string|max:255',
                'registration_language' => 'nullable|string|max:50',
                'registration_zone' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the broker type by name
            $brokerType = BrokerType::where('name', $request->broker_type_name)->first();
            
            if (!$brokerType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid broker type name',
                    'error' => 'The specified broker type does not exist'
                ], 422);
            }

            // Create the broker
            $broker = DB::transaction(function () use ($request, $brokerType) {

                $broker=Broker::create([
                    'broker_type_id' => $brokerType->id,
                    'registration_language' => $request->registration_language,
                    'registration_zone' => $request->registration_zone,
                ]);

                //insert broker option value trading_name
                //get the trading name option
               $tradingNameOption = BrokerOption::where('slug', 'trading_name')->first();
               OptionValue::create([
                'optionable_type' => Broker::class,
                'optionable_id' => $broker->id,
                'option_slug' => 'trading_name',
                'value' => $request->trading_name,
                'broker_id' => $broker->id,
                'broker_option_id' => $tradingNameOption->id,
            ]);

                //create a default team for the broker and add a user in that team
                $team = $this->teamService->createTeam([
                    'broker_id' => $broker->id,
                    'name' => 'Default Team',
                    'description' => 'Default team for the broker',
                    'permissions' => [],
                ]);
                $user = $this->teamService->createTeamUser([
                    'broker_team_id' => $team->id,
                    'name' => 'Default User',
                    'email' => $request->email,
                    'is_active' => true,
                ]);

                //generate user permission for the user
                $this->permissionService->createPermission([
                    'subject_type' => BrokerTeamUser::class,
                    'subject_id' => $user->id,
                    'permission_type' => 'broker',
                    'resource_id' => $broker->id,
                    'action' => 'manage',
                ]);

                //generate magic link for broker
                $magicLink = $this->magicLinkService->generateForTeamUser(
                    $user,
                    'registration',
                    ['requested_at' => now()],
                    96 // 96 hours = 4 days
                );

                //send email with magic link
               // Mail::to($user->email)->send(new MagicLinkMail($magicLink));

                return $broker;
            });

            // Load the broker type relationship
            $broker->load('brokerType','dynamicOptionsValues');

            return response()->json([
                'success' => true,
                'message' => 'Broker registered successfully',
                'data' => [
                    'broker' => $broker,
                    'broker_type' => $broker->brokerType
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Broker registration failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to register broker',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available broker types for registration
     */
    public function getBrokerTypes()
    {
        try {
            $brokerTypes = BrokerType::select('id', 'name')->get();

            return response()->json([
                'success' => true,
                'data' => $brokerTypes
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch broker types: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch broker types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send magic link to broker email (creates platform user for broker)
     */
    // public function sendMagicLink(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'broker_id' => 'required|exists:brokers,id',
    //             'action' => 'nullable|in:login,registration,password_reset',
    //             'email' => 'nullable|email',
    //             'expiration_hours' => 'nullable|integer|min:1|max:168', // Max 7 days
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation failed',
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $broker = Broker::findOrFail($request->broker_id);
    //         $action = $request->action ?? 'login';
    //         $expirationHours = $request->expiration_hours ?? 24;
    //         $email = $request->email ?? $broker->email ?? $broker->registration_language;

    //         // Create or find platform user for this broker
    //         $platformUser = PlatformUser::firstOrCreate(
    //             ['email' => $email],
    //             [
    //                 'name' => $broker->name ?? 'Broker User',
    //                 'broker_id' => $broker->id,
    //                 'is_active' => true,
    //             ]
    //         );

    //         // Generate magic link for platform user
    //         $magicLink = $this->magicLinkService->generateForPlatformUser(
    //             $platformUser, 
    //             $action, 
    //             ['requested_at' => now(), 'broker_id' => $broker->id],
    //             $expirationHours,
    //             $broker->id // context_broker_id
    //         );

    //         // Send email
    //         Mail::to($email)->send(new MagicLinkMail($magicLink));

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Magic link sent to your email',
    //             'data' => [
    //                 'broker_id' => $broker->id,
    //                 'platform_user_id' => $platformUser->id,
    //                 'action' => $action,
    //                 'expires_at' => $magicLink->expires_at,
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Failed to send magic link: ' . $e->getMessage());
            
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to send magic link',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

   
   
   
   
    

    /**
     * Send magic link for platform user
     */
    public function sendPlatformUserMagicLink(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'platform_user_id' => 'required|integer|exists:platform_users,id',
                'action' => 'sometimes|string|in:login,registration,password_reset',
                'expiration_hours' => 'sometimes|integer|min:1|max:168', // Max 1 week
                'context_broker_id' => 'sometimes|integer|exists:brokers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $platformUser = PlatformUser::findOrFail($request->platform_user_id);
            $action = $request->input('action', 'login');
            $expirationHours = $request->input('expiration_hours', 24);
            $contextBrokerId = $request->input('context_broker_id');

            $magicLink = $this->magicLinkService->generateForPlatformUser(
                $platformUser,
                $action,
                [],
                $expirationHours,
                $contextBrokerId
            );

            // Send email
            Mail::to($platformUser->email)->send(new MagicLinkMail($magicLink));

            return response()->json([
                'success' => true,
                'message' => 'Magic link sent successfully',
                'data' => [
                    'platform_user_id' => $platformUser->id,
                    'email' => $platformUser->email,
                    'action' => $action,
                    'expires_at' => $magicLink->expires_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Platform user magic link sending failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send magic link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get magic link statistics (admin only)
     */
    public function getMagicLinkStats()
    {
        try {
            $stats = $this->magicLinkService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get magic link stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get magic link statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke all magic links for a broker
     */
    public function revokeBrokerTokens(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'broker_id' => 'required|exists:brokers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $brokerId = $request->broker_id;

            // Revoke tokens for BrokerTeamUsers (through broker_teams relationship)
            $brokerTeamUsers = BrokerTeamUser::whereHas('team', function($query) use ($brokerId) {
                $query->where('broker_id', $brokerId);
            })->get();
            
            $teamRevokedCount = 0;
            foreach ($brokerTeamUsers as $teamUser) {
                $teamRevokedCount += $this->magicLinkService->cleanupExpiredTokensForTeamUser($teamUser->id);
            }

            return response()->json([
                'success' => true,
                'message' => "Revoked {$teamRevokedCount} magic links for team users",
                'data' => [
                    'broker_id' => $brokerId,
                    'revoked_count' => $teamRevokedCount,
                    'team_users_affected' => $brokerTeamUsers->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to revoke broker tokens: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke magic links',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke all magic links and delete all permissions for a platform user
     */
    public function revokePlatformUserTokens(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'platform_user_id' => 'required|exists:platform_users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $platformUser = PlatformUser::findOrFail($request->platform_user_id);
            
            // 1. Revoke all magic links for this platform user
            $revokedTokensCount = $this->magicLinkService->cleanupExpiredTokensForPlatformUser($platformUser->id);
            
            // 2. Get count of permissions before deletion
            $permissionCount = $platformUser->resourcePermissions()->count();
            
            // 3. Delete all permissions for this platform user
           // $deletedPermissionsCount = $platformUser->resourcePermissions()->delete();

            return response()->json([
                'success' => true,
                'message' => "Revoked {$revokedTokensCount} magic links tokens for platform user",
                'data' => [
                    'platform_user' => [
                        'id' => $platformUser->id,
                        'name' => $platformUser->name,
                        'email' => $platformUser->email,
                        'role' => $platformUser->role,
                    ],
                    'revoked_tokens_count' => $revokedTokensCount,
                    'previous_permissions_count' => $permissionCount,
                    'total_actions' => $revokedTokensCount,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to revoke platform user tokens and permissions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke platform user tokens and permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ===== TEAM MANAGEMENT ENDPOINTS =====

    /**
     * Create a new broker team
     */
    public function createTeam(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'broker_id' => 'required|exists:brokers,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'permissions' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $team = $this->teamService->createTeam($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Team created successfully',
                'data' => $team->load('broker')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create team: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create team',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teams for a broker
     */
    public function getBrokerTeams(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'broker_id' => 'required|exists:brokers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $teams = $this->teamService->getTeamsByBroker($request->broker_id);

            return response()->json([
                'success' => true,
                'data' => $teams
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get broker teams: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get teams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a team user
     */
    public function createTeamUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'broker_team_id' => 'required|exists:broker_teams,id',
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                  
                    //Rule::unique('platform_users', 'email'),
                    Rule::unique('broker_team_users', 'email'),
                ],
                'password' => 'nullable|string|min:8',
                'role' => 'required|in:admin,manager,member',
                'permissions' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $teamUser = $this->teamService->createTeamUser($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Team user created successfully',
                'data' => $teamUser->load('team.broker')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create team user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create team user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get team users
     */
    public function getTeamUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'broker_team_id' => 'required|exists:broker_teams,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $users = $this->teamService->getTeamUsers($request->broker_team_id);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get team users: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get team users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send magic link to team user
     */
    public function sendTeamUserMagicLink(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'team_user_id' => 'required|exists:broker_team_users,id',
                'action' => 'nullable|in:login,registration,password_reset',
                'expiration_hours' => 'nullable|integer|min:1|max:168',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $teamUser = BrokerTeamUser::with('team.broker')->findOrFail($request->team_user_id);
            $action = $request->action ?? 'login';
            $expirationHours = $request->expiration_hours ?? 24;

            // Generate magic link
            $magicLink = $this->magicLinkService->generateForTeamUser(
                $teamUser,
                $action,
                ['requested_at' => now()],
                $expirationHours
            );

            // Send email
            Mail::to($teamUser->email)->send(new MagicLinkMail($magicLink));

            return response()->json([
                'success' => true,
                'message' => 'Magic link sent to team user email',
                'data' => [
                    'team_user_id' => $teamUser->id,
                    'action' => $action,
                    'expires_at' => $magicLink->expires_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send team user magic link: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send magic link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available roles and permissions
     */
    public function getTeamRolesAndPermissions()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'roles' => $this->teamService->getAvailableRoles(),
                    'permissions' => $this->teamService->getAvailablePermissions(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get roles and permissions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get roles and permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== RESOURCE PERMISSION MANAGEMENT ====================

    /**
     * Create a resource permission for a team user.
     */
    public function createResourcePermission(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'permission_type' => 'required|string|in:broker,country,zone,broker_type',
                'resource_id' => 'nullable|integer',
                'resource_value' => 'nullable|string|max:255',
                'action' => 'required|string|in:view,edit,delete,manage',
                'metadata' => 'nullable|array',
                'is_active' => 'boolean',
            ]);

            $permission = $this->permissionService->createPermission($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Resource permission created successfully',
                'data' => $permission
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create resource permission: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create resource permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resource permissions for a team user.
     */
    public function getResourcePermissions(Request $request, int $teamUserId)
    {
        try {
            $filters = $request->only(['permission_type', 'action', 'is_active', 'resource_id', 'resource_value']);
            
            $permissions = $this->permissionService->getFilteredPermissions($teamUserId, $filters);

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get resource permissions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get resource permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a resource permission.
     */
    public function updateResourcePermission(Request $request, int $permissionId)
    {
        try {
            $validatedData = $request->validate([
                'permission_type' => 'sometimes|string|in:broker,country,zone,broker_type',
                'resource_id' => 'nullable|integer',
                'resource_value' => 'nullable|string|max:255',
                'action' => 'sometimes|string|in:view,edit,delete,manage',
                'metadata' => 'nullable|array',
                'is_active' => 'boolean',
            ]);

            $permission = $this->permissionService->updatePermission($permissionId, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Resource permission updated successfully',
                'data' => $permission
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update resource permission: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update resource permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a resource permission.
     */
    public function deleteResourcePermission(int $permissionId)
    {
        try {
            $deleted = $this->permissionService->deletePermission($permissionId);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Resource permission deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource permission not found'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Failed to delete resource permission: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete resource permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle permission active status.
     */
    public function togglePermissionActive(int $permissionId)
    {
        try {
            $permission = $this->permissionService->togglePermissionActive($permissionId);

            return response()->json([
                'success' => true,
                'message' => 'Permission status updated successfully',
                'data' => $permission
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to toggle permission active status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle permission active status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permission statistics for a team user.
     */
    public function getPermissionStats(int $teamUserId)
    {
        try {
            $stats = $this->permissionService->getPermissionStats($teamUserId);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get permission stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get permission stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign broker permission to team user.
     */
    public function assignBrokerPermission(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'broker_id' => 'required|integer',
                'action' => 'required|string|in:view,edit,delete,manage',
            ]);

            $permission = $this->permissionService->assignBrokerPermission(
                $validatedData['broker_team_user_id'],
                $validatedData['broker_id'],
                $validatedData['action']
            );

            return response()->json([
                'success' => true,
                'message' => 'Broker permission assigned successfully',
                'data' => $permission
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to assign broker permission: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign broker permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign country permission to team user.
     */
    public function assignCountryPermission(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'country' => 'required|string|max:255',
                'action' => 'required|string|in:view,edit,delete,manage',
            ]);

            $permission = $this->permissionService->assignCountryPermission(
                $validatedData['broker_team_user_id'],
                $validatedData['country'],
                $validatedData['action']
            );

            return response()->json([
                'success' => true,
                'message' => 'Country permission assigned successfully',
                'data' => $permission
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to assign country permission: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign country permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign zone permission to team user.
     */
    public function assignZonePermission(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'zone' => 'required|string|max:255',
                'action' => 'required|string|in:view,edit,delete,manage',
            ]);

            $permission = $this->permissionService->assignZonePermission(
                $validatedData['broker_team_user_id'],
                $validatedData['zone'],
                $validatedData['action']
            );

            return response()->json([
                'success' => true,
                'message' => 'Zone permission assigned successfully',
                'data' => $permission
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to assign zone permission: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign zone permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available permission types and actions.
     */
    public function getPermissionOptions()
    {
        try {
            $options = [
                'permission_types' => $this->permissionService->getAvailablePermissionTypes(),
                'actions' => $this->permissionService->getAvailableActions(),
            ];

            return response()->json([
                'success' => true,
                'data' => $options
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get permission options: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get permission options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== SUPER ADMIN FUNCTIONS ====================

    /**
     * Assign country management to a user (Super Admin only).
     */
    public function assignCountryManagement(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'country' => 'required|string|max:255',
                'options' => 'nullable|array',
            ]);

            $permission = $this->superAdminService->assignCountryManagement(
                $validatedData['broker_team_user_id'],
                $validatedData['country'],
                $validatedData['options'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Country management assigned successfully',
                'data' => $permission
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to assign country management: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign country management',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign zone management to a user (Super Admin only).
     */
    public function assignZoneManagement(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'zone' => 'required|string|max:255',
                'options' => 'nullable|array',
            ]);

            $permission = $this->superAdminService->assignZoneManagement(
                $validatedData['broker_team_user_id'],
                $validatedData['zone'],
                $validatedData['options'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Zone management assigned successfully',
                'data' => $permission
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to assign zone management: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign zone management',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign global broker management to a user (Super Admin only).
     */
    public function assignGlobalBrokerManagement(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'options' => 'nullable|array',
            ]);

            $permission = $this->superAdminService->assignGlobalBrokerManagement(
                $validatedData['broker_team_user_id'],
                $validatedData['options'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Global broker management assigned successfully',
                'data' => $permission
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to assign global broker management: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign global broker management',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get manageable brokers for a user.
     */
    public function getManageableBrokers(int $teamUserId)
    {
        try {
            $brokers = $this->superAdminService->getManageableBrokers($teamUserId);

            return response()->json([
                'success' => true,
                'data' => $brokers
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get manageable brokers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get manageable brokers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get management statistics for a user.
     */
    public function getManagementStats(int $teamUserId)
    {
        try {
            $stats = $this->superAdminService->getManagementStats($teamUserId);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get management stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get management stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign country management to multiple users.
     */
    public function bulkAssignCountryManagement(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'assignments' => 'required|array|min:1',
                'assignments.*.broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'assignments.*.country' => 'required|string|max:255',
                'assignments.*.options' => 'nullable|array',
            ]);

            $results = $this->superAdminService->bulkAssignCountryManagement($validatedData['assignments']);

            return response()->json([
                'success' => true,
                'message' => 'Bulk country management assignment completed',
                'data' => $results
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to bulk assign country management: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk assign country management',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke country management from a user.
     */
    public function revokeCountryManagement(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'broker_team_user_id' => 'required|integer|exists:broker_team_users,id',
                'country' => 'required|string|max:255',
            ]);

            $revoked = $this->superAdminService->revokeCountryManagement(
                $validatedData['broker_team_user_id'],
                $validatedData['country']
            );

            if ($revoked) {
                return response()->json([
                    'success' => true,
                    'message' => 'Country management revoked successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No country management found to revoke'
                ], 404);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to revoke country management: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke country management',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * OK
     * Verify magic link token and return user data for frontend authentication
     */
    public function verifyMagicLinkToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string|size:64',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            
            // Validate the magic link token
            $magicLink = $this->magicLinkService->validateToken($request->token);
           

            if (!$magicLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired magic link',
                    'error' => 'The magic link is invalid or has expired. Please request a new one.'
                ], 400);
            }

            // Mark the magic link as used (unless disabled for testing)
            if (config('auth.mark_magic_link_as_used', true)) {
                $this->magicLinkService->markAsUsed($magicLink);
            }

            // Get the subject (user) from the magic link
            $subject = $magicLink->subject;
            
            
            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user',
                    'error' => 'The magic link is associated with an invalid user.'
                ], 400);
            }

            // Update last login
            $subject->update(['last_login_at' => now()]);

            // Generate Sanctum token with configurable expiration
            // Option 1: Use config value
            $jwtExpirationDays = (int) config('auth.jwt_expiration_days', 7); // Default 7 days
            
            // Option 2: Match magic link expiration (uncomment to use)
            // $jwtExpirationDays = (int) $magicLink->expires_at->diffInDays(now());
            
            // Generate the Sanctum token
            $token = $subject->createToken('magic-link-auth', ['*'], now()->addDays($jwtExpirationDays))->plainTextToken;
          
            // Prepare user data based on type
            $userData = [
                'id' => $subject->id,
                'name' => $subject->name,
                'email' => $subject->email
            ];

           

            // if ($subject instanceof \Modules\Auth\Models\BrokerTeamUser) {
            //     $userData['user_type'] = 'team_user';
            //     $userData['broker_context'] = [
            //         'broker_id' => $subject->team->broker_id,
            //         'broker_name' => $subject->team->broker->trading_name ?? $subject->team->broker->name,
            //         'team_id' => $subject->broker_team_id,
            //         'team_name' => $subject->team->name,
            //     ];
            //     //$userData['role'] = $subject->role;
            //     $userData['permissions'] = $subject->resourcePermissions->map(function($permission) {
            //         return [
            //             'type' => $permission->permission_type,
            //             'resource_id' => $permission->resource_id,
            //             'resource_value' => $permission->resource_value,
            //             'action' => $permission->action,
            //         ];
            //     })->toArray();
            // } elseif ($subject instanceof \Modules\Auth\Models\PlatformUser) {
            //     $userData['user_type'] = 'platform_user';
            //     $userData['role'] = $subject->role;
            //     $userData['broker_context'] = $subject->broker_id ? [
            //         'broker_id' => $subject->broker_id,
            //     ] : null;
            //     $userData['permissions'] = $subject->resourcePermissions->map(function($permission) {
            //         return [
            //             'type' => $permission->permission_type,
            //             'resource_id' => $permission->resource_id,
            //             'resource_value' => $permission->resource_value,
            //             'action' => $permission->action,
            //         ];
            //     })->toArray();
            // }

            return response()->json([
                'success' => true,
                'message' => 'Magic link verified successfully',
                'data' => [
                    'user' => $userData,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => now()->addDays($jwtExpirationDays)->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Magic link token verification failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'error' => 'An error occurred while verifying your magic link. Please try again.'
            ], 500);
        }
    }

    /**
     * Decode JWT token and return user data (for frontend authentication)
     */
    public function decodeToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the Sanctum token
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->token);
            
            if (!$personalAccessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token',
                    'error' => 'Token not found'
                ], 401);
            }

            // Check if token is expired
            if ($personalAccessToken->expires_at && $personalAccessToken->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token expired',
                    'error' => 'Token has expired'
                ], 401);
            }

            // Get the user from the token
            $user = $personalAccessToken->tokenable;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'error' => 'User associated with token not found'
                ], 401);
            }

            // Prepare user data based on type
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => null,
                'permissions' => [],
                'broker_context' => null,
            ];

            if ($user instanceof \Modules\Auth\Models\BrokerTeamUser) {
                $userData['user_type'] = 'team_user';
                $userData['broker_context'] = [
                    'broker_id' => $user->team->broker_id,
                    'broker_name' => $user->team->broker->trading_name ?? $user->team->broker->name,
                    'team_id' => $user->broker_team_id,
                    'team_name' => $user->team->name,
                ];
                $userData['permissions'] = $user->resourcePermissions->map(function($permission) {
                    return [
                        'type' => $permission->permission_type,
                        'resource_id' => $permission->resource_id,
                        'resource_value' => $permission->resource_value,
                        'action' => $permission->action,
                    ];
                })->toArray();
            } elseif ($user instanceof \Modules\Auth\Models\PlatformUser) {
                $userData['user_type'] = 'platform_user';
                $userData['role'] = $user->role;
                $userData['broker_context'] = $user->broker_id ? [
                    'broker_id' => $user->broker_id,
                ] : null;
                $userData['permissions'] = $user->resourcePermissions->map(function($permission) {
                    return [
                        'type' => $permission->permission_type,
                        'resource_id' => $permission->resource_id,
                        'resource_value' => $permission->resource_value,
                        'action' => $permission->action,
                    ];
                })->toArray();
            }

            return response()->json([
                'success' => true,
                'user' => $userData,
                'token_expires_at' => $personalAccessToken->expires_at?->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Token decoding failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Token validation failed',
                'error' => 'An error occurred while validating the token'
            ], 401);
        }
    }
}
