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
use Modules\Auth\Http\Requests\RegisterBrokerRequest;

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
     * OK
     * Register a new broker
     */
    public function registerBroker(RegisterBrokerRequest $request)
    {
        try {

            // Create the broker
            $broker = DB::transaction(function () use ($request) {

                $broker = Broker::create([
                    'broker_type_id' => $request->broker_type_id,
                    'country_id' => $request->country_id,
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
                    'name' => 'Broker Admin',
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
            $broker->load('brokerType', 'dynamicOptionsValues');

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
            $expirationDays = (int) config('auth.jwt_expiration_days', 7); // Default 7 days

            // Option 2: Match magic link expiration (uncomment to use)
            // $jwtExpirationDays = (int) $magicLink->expires_at->diffInDays(now());

            // Generate the Sanctum token
            $token = $subject->createToken('magic-link-auth', ['*'], now()->addDays($expirationDays))->plainTextToken;

            // Prepare user data based on type
            $userData = [
                'id' => $subject->id,
                'name' => $subject->name,
                'email' => $subject->email
            ];


            if ($subject instanceof \Modules\Auth\Models\BrokerTeamUser) {
                $userData['user_type'] = 'team_user';
                $brokerContext = [
                    'broker_id' => $subject->team->broker_id,
                    'broker_name' => $subject->team->broker->trading_name ?? $subject->team->broker->name,
                    'team_id' => $subject->broker_team_id,
                    'team_name' => $subject->team->name,
                ];
            } elseif ($subject instanceof \Modules\Auth\Models\PlatformUser) {
                $userData['user_type'] = 'platform_user';
            }

            

            return response()->json([
                'success' => true,
                'message' => 'Magic link verified successfully',
                'data' => [
                    'user' => $userData,
                    'broker_context' => $brokerContext,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => now()->addDays($expirationDays)->toISOString(),
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
     * ??
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
                $userData['permissions'] = $user->resourcePermissions->map(function ($permission) {
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
                $userData['permissions'] = $user->resourcePermissions->map(function ($permission) {
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

    /**
     * OK
     * Register a new user to the broker default team
     */
    public function registerUserToBrokerDefaultTeam(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'broker_id' => 'required|exists:brokers,id',
            'email' => 'required|email|unique:broker_team_users,email',
            'name' => 'required|string|max:255',
            'permission_action' => 'required|string|in:manage,view,edit,delete',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $teamUser = BrokerTeamUser::where('email', $request->email)->first();

        if ($teamUser) {
            return response()->json([
                'success' => false,
                'message' => 'Team user already exists',
            ], 404);
        }

        $defaultTeam = BrokerTeam::where('broker_id', $request->broker_id)->where('name', 'Default Team')->first();

        if (!$defaultTeam) {
            return response()->json([
                'success' => false,
                'message' => 'Default team not found',
            ], 404);
        }

        try {
            DB::transaction(function () use ($request, $defaultTeam) {
                $teamUser = $this->teamService->createTeamUser([
                    'broker_team_id' => $defaultTeam->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'is_active' => true,
                ]);
                //add permission to user
                $this->permissionService->createPermission([
                    'subject_type' => BrokerTeamUser::class,
                    'subject_id' => $teamUser->id,
                    'permission_type' => 'broker',
                    'resource_id' => $request->broker_id,
                    'action' => $request->permission_action,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to add team user to default team: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add team user to default team',
                'error' => $e->getMessage()
            ], 500);
        }


        return response()->json([
            'success' => true,
            'message' => 'Team user added to default team successfully',
            'data' => $teamUser,
        ]);
    }

    public function getBrokerDefaultTeam(Request $request,int $brokerId)
    {
        try {
        $defaultTeam = BrokerTeam::where('broker_id', $brokerId)->where('name', 'Default Team')->first();

        if (!$defaultTeam) {
            return response()->json([
                'success' => false,
                'message' => 'Default team not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Default team found',
            'data' => $defaultTeam->load('users.resourcePermissions'),
        ]);
        } catch (\Exception $e) {
           
            return response()->json([
                'success' => false,
                'message' => 'Failed to get broker default team',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ok
     * Update a broker team user's data
     */
    public function updateBrokerTeamUser(Request $request, $userId)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('broker_team_users', 'email')->ignore($userId),
                ],
                'is_active' => 'sometimes|boolean',
                'permission_action' => 'sometimes|string|in:manage,view,edit,delete',
                //'broker_id' => 'sometimes|required|exists:brokers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the team user
            $teamUser = BrokerTeamUser::find($userId);

            if (!$teamUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team user not found',
                ], 404);
            }

            DB::transaction(function () use ($request, $teamUser) {
                // Update user data
                $updateData = [];
                
                if ($request->has('name')) {
                    $updateData['name'] = $request->name;
                }
                
                if ($request->has('email')) {
                    $updateData['email'] = $request->email;
                }
                
                if ($request->has('is_active')) {
                    $updateData['is_active'] = $request->is_active;
                }
                
               

                if (!empty($updateData)) {
                    $teamUser->update($updateData);
                }

                // Update permission if provided
                if ($request->has('permission_action') && $request->has('broker_id')) {

                    $brokerId = $teamUser->team->broker_id;
                    // Find existing permission for this user and broker
                    $permission = \Modules\Auth\Models\UserPermission::where('subject_type', BrokerTeamUser::class)
                        ->where('subject_id', $teamUser->id)
                        ->where('permission_type', 'broker')
                        ->where('resource_id', $brokerId)
                        ->first();

                    if ($permission) {
                        // Update existing permission
                        $permission->update([
                            'action' => $request->permission_action,
                        ]);
                    } else {
                        // Create new permission
                        $this->permissionService->createPermission([
                            'subject_type' => BrokerTeamUser::class,
                            'subject_id' => $teamUser->id,
                            'permission_type' => 'broker',
                            'resource_id' => $brokerId,
                            'action' => $request->permission_action,
                        ]);
                    }
                }
            });

            // Reload the team user with relationships
            $teamUser->load('team.broker', 'resourcePermissions');

            return response()->json([
                'success' => true,
                'message' => 'Team user updated successfully',
                'data' => $teamUser,
            ]);

        } catch (\Exception $e) {
           // Log::error('Failed to update team user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update team user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ok
     * Delete a broker team user
     */
    public function deleteBrokerTeamUser(Request $request, $userId)
    {
        try {
            $teamUser = BrokerTeamUser::find($userId);

            if (!$teamUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team user not found',
                ], 404);
            }

            DB::transaction(function () use ($teamUser) {
                // Delete all related permissions
                $teamUser->resourcePermissions()->delete();
                
                // Delete all related magic links
                $teamUser->magicLinks()->delete();
                
                // Delete the user
                $teamUser->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Team user deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete team user',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
