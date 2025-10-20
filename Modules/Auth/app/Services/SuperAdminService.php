<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\BrokerTeamUser;
use Modules\Auth\Models\UserPermission;
use Modules\Auth\Services\UserPermissionService;
use Modules\Brokers\Models\Broker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuperAdminService
{
    protected UserPermissionService $permissionService;

    public function __construct(UserPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Assign country management to a user (SaaS super admin function).
     */
    public function assignCountryManagement(int $teamUserId, string $country, array $options = []): UserPermission
    {
        try {
            DB::beginTransaction();

            // Create country management permission
            $permission = $this->permissionService->assignCountryPermission(
                $teamUserId,
                $country,
                'manage'
            );

            // Add metadata for additional context
            $permission->update([
                'metadata' => array_merge([
                    'assigned_by' => 'super_admin',
                    'assigned_at' => now()->toISOString(),
                    'scope' => 'country_management',
                    'country_name' => $this->getCountryName($country),
                ], $options)
            ]);

            // Log the assignment
            Log::info('Country management assigned by super admin', [
                'team_user_id' => $teamUserId,
                'country' => $country,
                'permission_id' => $permission->id,
            ]);

            DB::commit();
            return $permission;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign country management', [
                'team_user_id' => $teamUserId,
                'country' => $country,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Assign zone management to a user (SaaS super admin function).
     */
    public function assignZoneManagement(int $teamUserId, string $zone, array $options = []): UserPermission
    {
        try {
            DB::beginTransaction();

            $permission = $this->permissionService->assignZonePermission(
                $teamUserId,
                $zone,
                'manage'
            );

            $permission->update([
                'metadata' => array_merge([
                    'assigned_by' => 'super_admin',
                    'assigned_at' => now()->toISOString(),
                    'scope' => 'zone_management',
                    'zone_name' => $this->getZoneName($zone),
                ], $options)
            ]);

            Log::info('Zone management assigned by super admin', [
                'team_user_id' => $teamUserId,
                'zone' => $zone,
                'permission_id' => $permission->id,
            ]);

            DB::commit();
            return $permission;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign zone management', [
                'team_user_id' => $teamUserId,
                'zone' => $zone,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Assign global broker management to a user (SaaS super admin function).
     */
    public function assignGlobalBrokerManagement(int $teamUserId, array $options = []): UserPermission
    {
        try {
            DB::beginTransaction();

            // Create a special permission for global access
            $permission = $this->permissionService->createPermission([
                'broker_team_user_id' => $teamUserId,
                'permission_type' => 'broker',
                'resource_id' => null, // null means all brokers
                'resource_value' => '*', // wildcard for all
                'action' => 'manage',
                'metadata' => array_merge([
                    'assigned_by' => 'super_admin',
                    'assigned_at' => now()->toISOString(),
                    'scope' => 'global_management',
                    'description' => 'Full access to all brokers',
                ], $options)
            ]);

            Log::info('Global broker management assigned by super admin', [
                'team_user_id' => $teamUserId,
                'permission_id' => $permission->id,
            ]);

            DB::commit();
            return $permission;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign global broker management', [
                'team_user_id' => $teamUserId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get all brokers a user can manage based on their permissions.
     */
    public function getManageableBrokers(int $teamUserId): \Illuminate\Database\Eloquent\Collection
    {
        $user = BrokerTeamUser::findOrFail($teamUserId);
        
        // Get all broker IDs the user can manage
        $brokerIds = $user->getAccessibleBrokerIds();
        
        // Get countries the user can manage
        $countries = $user->getAccessibleCountries();
        
        // Get zones the user can manage
        $zones = $user->getAccessibleZones();
        
        // Get broker types the user can manage
        $brokerTypes = $user->getAccessibleBrokerTypes();

        $query = Broker::query();

        // If user has specific broker permissions, include those
        if (!empty($brokerIds)) {
            $query->whereIn('id', $brokerIds);
        }

        // If user has country permissions, include brokers from those countries
        if (!empty($countries)) {
            $query->orWhereIn('registration_zone', $countries);
        }

        // If user has zone permissions, include brokers from those zones
        if (!empty($zones)) {
            $query->orWhereIn('registration_zone', $zones);
        }

        // If user has broker type permissions, include brokers of those types
        if (!empty($brokerTypes)) {
            $query->orWhereHas('brokerType', function ($q) use ($brokerTypes) {
                $q->whereIn('name', $brokerTypes);
            });
        }

        return $query->with(['brokerType'])->get();
    }

    /**
     * Get management statistics for a user.
     */
    public function getManagementStats(int $teamUserId): array
    {
        $user = BrokerTeamUser::findOrFail($teamUserId);
        $manageableBrokers = $this->getManageableBrokers($teamUserId);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'permissions' => $user->getResourcePermissionSummary(),
            'manageable_brokers' => [
                'total' => $manageableBrokers->count(),
                'by_country' => $manageableBrokers->groupBy('registration_zone'),
                'by_type' => $manageableBrokers->groupBy('broker_type_id'),
            ],
            'access_level' => $this->getUserAccessLevel($user),
        ];
    }

    /**
     * Determine user's access level.
     */
    private function getUserAccessLevel(BrokerTeamUser $user): string
    {
        $permissions = $user->resourcePermissions()->active()->get();

        // Check for global access
        $hasGlobalAccess = $permissions->where('permission_type', 'broker')
                                     ->where('resource_value', '*')
                                     ->where('action', 'manage')
                                     ->isNotEmpty();

        if ($hasGlobalAccess) {
            return 'global_admin';
        }

        // Check for country management
        $hasCountryManagement = $permissions->where('permission_type', 'country')
                                          ->where('action', 'manage')
                                          ->isNotEmpty();

        if ($hasCountryManagement) {
            return 'country_manager';
        }

        // Check for zone management
        $hasZoneManagement = $permissions->where('permission_type', 'zone')
                                       ->where('action', 'manage')
                                       ->isNotEmpty();

        if ($hasZoneManagement) {
            return 'zone_manager';
        }

        // Check for specific broker management
        $hasBrokerManagement = $permissions->where('permission_type', 'broker')
                                         ->where('action', 'manage')
                                         ->isNotEmpty();

        if ($hasBrokerManagement) {
            return 'broker_manager';
        }

        return 'limited_access';
    }

    /**
     * Get country name from country code.
     */
    private function getCountryName(string $countryCode): string
    {
        $countries = [
            'US' => 'United States',
            'CA' => 'Canada',
            'UK' => 'United Kingdom',
            'DE' => 'Germany',
            'FR' => 'France',
            'AU' => 'Australia',
            'JP' => 'Japan',
            'BR' => 'Brazil',
            'IN' => 'India',
            'CN' => 'China',
        ];

        return $countries[$countryCode] ?? $countryCode;
    }

    /**
     * Get zone name from zone code.
     */
    private function getZoneName(string $zoneCode): string
    {
        $zones = [
            'americas' => 'Americas',
            'europe' => 'Europe',
            'asia' => 'Asia',
            'africa' => 'Africa',
            'oceania' => 'Oceania',
        ];

        return $zones[$zoneCode] ?? $zoneCode;
    }

    /**
     * Bulk assign country management to multiple users.
     */
    public function bulkAssignCountryManagement(array $assignments): array
    {
        $results = [];
        
        foreach ($assignments as $assignment) {
            try {
                $permission = $this->assignCountryManagement(
                    $assignment['team_user_id'],
                    $assignment['country'],
                    $assignment['options'] ?? []
                );
                
                $results[] = [
                    'success' => true,
                    'team_user_id' => $assignment['team_user_id'],
                    'country' => $assignment['country'],
                    'permission_id' => $permission->id,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'team_user_id' => $assignment['team_user_id'],
                    'country' => $assignment['country'],
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }

    /**
     * Revoke country management from a user.
     */
    public function revokeCountryManagement(int $teamUserId, string $country): bool
    {
        try {
            $deleted = UserPermission::where('broker_team_user_id', $teamUserId)
                                             ->where('permission_type', 'country')
                                             ->where('resource_value', $country)
                                             ->where('action', 'manage')
                                             ->delete();

            Log::info('Country management revoked by super admin', [
                'team_user_id' => $teamUserId,
                'country' => $country,
                'deleted_count' => $deleted,
            ]);

            return $deleted > 0;
        } catch (\Exception $e) {
            Log::error('Failed to revoke country management', [
                'team_user_id' => $teamUserId,
                'country' => $country,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
