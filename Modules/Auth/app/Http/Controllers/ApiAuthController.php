<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
use Modules\Auth\Http\Requests\VerifyMagicLinkTokenRequest;
use Modules\Auth\Http\Requests\DecodeTokenRequest;
use Modules\Auth\Http\Requests\LoginWithEmailRequest;
use Modules\Auth\Enums\AuthUser;


class ApiAuthController extends Controller
{
    protected MagicLinkService $magicLinkService;
      

    public function __construct(
        MagicLinkService $magicLinkService, 
    ) {
        $this->magicLinkService = $magicLinkService;
    }



    /**
     * OK
     * Verify magic link token and return user data for frontend authentication
     */
    public function verifyMagicLinkToken(VerifyMagicLinkTokenRequest $request)
    {
        try {
            // Validate the magic link token
            $magicLink = $this->magicLinkService->validateToken($request->token);
            if (!$magicLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired magic link',
                    'error' => 'The magic link is invalid or has expired. Please request a new one.'
                ], 400);
            }

            [$userData,$brokerContext,$token]=DB::transaction(function() use ($magicLink) {
            // Mark the magic link as used (unless disabled for testing)
            if (config('auth.mark_magic_link_as_used', true)) {
                $this->magicLinkService->markAsUsed($magicLink);
            }
            // Get the subject (user) from the magic link
            $subject = $magicLink->subject;
            if (!$subject) {
                throw new \Exception('Invalid user');
            }

            // Update last login
            $subject->update(['last_login_at' => now()]);

            // Generate Sanctum token with configurable expiration
            // Option 1: Use config value
            $expirationDays = (int) config('auth.jwt_expiration_days', 7); // Default 7 days

            //EXAMPLE OF A SANCTUM PERMISSIONS ARRAY. SUPER ADMIN HAS ALL PERMISSIONS I.E. ['*']
            // [
            //     'super-admin',
            //     'broker:view:1',
            //     'country:view:1',
            //     'zone:view:1',
            //     'seo:view:1',
            //     'translator:view:1',
            // ]
            // Generate the Sanctum token
            $token = $subject->createToken('magic-link-auth', $subject->getSanctumPermissions(), now()->addDays($expirationDays))->plainTextToken;
   

            // Prepare user data based on type
            $userData = [
                'id' => $subject->id,
                'name' => $subject->name,
                'email' => $subject->email,
                'role' => $subject->role,
                'sanctum_permissions' => $subject->getSanctumPermissions(),
                'permissions' => $subject->getPermissionsArray(),
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
                $brokerContext = [];
            }


            return [$userData,$brokerContext,$token];
        });
        //end of transaction

        if(!$userData || !$token){
            throw new \Exception('Failed to verify magic link');
        }

        return response()->json([
            'success' => true,
            'message' => 'Magic link verified successfully',
            
            'data' => [
                'user' => $userData,
                'broker_context' => $brokerContext??null,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(config('auth.jwt_expiration_days', 7))->toISOString(),
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
     * OK
     * Login with email and send magic link to user
     */
    public function loginWithEmail(LoginWithEmailRequest $request)
    {
        
        try {
            $brokerTeamUser = BrokerTeamUser::where('email', $request->email)->first();

            if ($brokerTeamUser instanceof BrokerTeamUser) {
                $expirationHours = config('auth.magic_link_expiration_hours_for_broker_team_user', 72);
                $magicLink = $this->magicLinkService->createMagicLink(AuthUser::BROKER_TEAM_USER, $brokerTeamUser, 'login', [], $expirationHours);
                Mail::to($brokerTeamUser->email)->send(new MagicLinkMail($magicLink));

                return response()->json([
                    'success' => true,
                    'message' => 'Magic link created successfully',
                    'data' => $magicLink
                ], 200);
            }

            $platformUser = PlatformUser::where('email', $request->email)->first();

            if ($platformUser instanceof PlatformUser) {
                $expirationHours = config('auth.magic_link_expiration_hours_for_platform_user', 72);
                //$magicLink = $this->magicLinkService->sendMagicLinkToPlatformUser($platformUser, 'login');
                $magicLink = $this->magicLinkService->createMagicLink(AuthUser::PLATFORM_USER, $platformUser, 'login', [], $expirationHours);
                Mail::to($platformUser->email)->send(new MagicLinkMail($magicLink));
                return response()->json([
                    'success' => true,
                    'message' => 'Magic link created successfully',
                    'data' => $magicLink
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Login with email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => 'An error occurred while logging in with email. Please try again.'
            ], 400);
        }
    }

    // /**
    //  * ?
    //  * Decode JWT token and return user data (for frontend authentication)
    //  */
    // public function decodeToken(DecodeTokenRequest $request)
    // {
    //     try {
    //         // Find the Sanctum token
    //         $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->token);

    //         if (!$personalAccessToken) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid token',
    //                 'error' => 'Token not found'
    //             ], 401);
    //         }

    //         // Check if token is expired
    //         if ($personalAccessToken->expires_at && $personalAccessToken->expires_at->isPast()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Token expired',
    //                 'error' => 'Token has expired'
    //             ], 401);
    //         }

    //         // Get the user from the token
    //         $user = $personalAccessToken->tokenable;

    //         if (!$user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'User not found',
    //                 'error' => 'User associated with token not found'
    //             ], 401);
    //         }

    //         // Prepare user data based on type
    //         $userData = [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'user_type' => null,
    //             'permissions' => [],
    //             'broker_context' => null,
    //         ];

    //         if ($user instanceof \Modules\Auth\Models\BrokerTeamUser) {
    //             $userData['user_type'] = 'team_user';
    //             $userData['broker_context'] = [
    //                 'broker_id' => $user->team->broker_id,
    //                 'broker_name' => $user->team->broker->trading_name ?? $user->team->broker->name,
    //                 'team_id' => $user->broker_team_id,
    //                 'team_name' => $user->team->name,
    //             ];
    //             $userData['permissions'] = $user->resourcePermissions->map(function ($permission) {
    //                 return [
    //                     'type' => $permission->permission_type,
    //                     'resource_id' => $permission->resource_id,
    //                     'resource_value' => $permission->resource_value,
    //                     'action' => $permission->action,
    //                 ];
    //             })->toArray();
    //         } elseif ($user instanceof \Modules\Auth\Models\PlatformUser) {
    //             $userData['user_type'] = 'platform_user';
    //             $userData['role'] = $user->role;
    //             $userData['broker_context'] = $user->broker_id ? [
    //                 'broker_id' => $user->broker_id,
    //             ] : null;
    //             $userData['permissions'] = $user->resourcePermissions->map(function ($permission) {
    //                 return [
    //                     'type' => $permission->permission_type,
    //                     'resource_id' => $permission->resource_id,
    //                     'resource_value' => $permission->resource_value,
    //                     'action' => $permission->action,
    //                 ];
    //             })->toArray();
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'user' => $userData,
    //             'token_expires_at' => $personalAccessToken->expires_at?->toISOString(),
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Token decoding failed: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Token validation failed',
    //             'error' => 'An error occurred while validating the token'
    //         ], 401);
    //     }
    // }
}
