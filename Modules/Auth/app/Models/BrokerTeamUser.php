<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Auth\Models\MagicLink;
use Modules\Brokers\Models\Broker;
use Modules\Auth\Models\BrokerTeam;
use Modules\Auth\Models\UserPermission;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class BrokerTeamUser extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $fillable = [
        'broker_team_id',
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the team that the user belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(BrokerTeam::class, 'broker_team_id');
    }

    /**
     * Get the broker through the team.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class, 'broker_id', 'id')
            ->through('team');
    }

    /**
     * Deprecated: Get magic links for this user.
     */
    public function magicLinksBuilder(): HasMany
    {
        return $this->hasMany(MagicLink::class, 'subject_id')
                    ->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser');
    }

    /**
     * Get magic links for this user.
     * @return MorphMany
     * @throws \Exception
     */
    public function magicLinks(): MorphMany
    {
        return $this->morphMany(MagicLink::class, 'subject');
    }

    /**
     * Get resource permissions for this user.
     */
    public function resourcePermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class, 'subject_id')
                    ->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser');
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Check user-specific permissions first
        $userPermissions = $this->permissions ?? [];
        if (in_array($permission, $userPermissions) || in_array('*', $userPermissions)) {
            return true;
        }

        // Check team permissions
        return $this->team?->hasPermission($permission) ?? false;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is manager.
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Add permission to user.
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Remove permission from user.
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Scope for active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    

    // ==================== RESOURCE PERMISSION METHODS ====================

    /**
     * Check if user can access specific broker.
     */
    public function canAccessBroker(int $brokerId): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'broker')
                   ->where('resource_id', $brokerId)
                   ->where('is_active', true)
                   ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                   ->exists();
    }

    /**
     * Check if user can edit specific broker.
     */
    public function canEditBroker(int $brokerId): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'broker')
                   ->where('resource_id', $brokerId)
                   ->where('is_active', true)
                   ->whereIn('action', ['edit', 'manage'])
                   ->exists();
    }

    /**
     * Check if user can manage specific broker.
     */
    public function canManageBroker(int $brokerId): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'broker')
                   ->where('resource_id', $brokerId)
                   ->where('is_active', true)
                   ->where('action', 'manage')
                   ->exists();
    }

    /**
     * Check if user can access brokers from specific country.
     */
    public function canAccessCountry(string $country): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'country')
                   ->where('resource_value', $country)
                   ->where('is_active', true)
                   ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                   ->exists();
    }

    /**
     * Check if user can edit brokers from specific country.
     */
    public function canEditCountry(string $country): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'country')
                   ->where('resource_value', $country)
                   ->where('is_active', true)
                   ->whereIn('action', ['edit', 'manage'])
                   ->exists();
    }

    /**
     * Check if user can access brokers from specific zone.
     */
    public function canAccessZone(string $zone): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'zone')
                   ->where('resource_value', $zone)
                   ->where('is_active', true)
                   ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                   ->exists();
    }

    /**
     * Check if user can edit brokers from specific zone.
     */
    public function canEditZone(string $zone): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'zone')
                   ->where('resource_value', $zone)
                   ->where('is_active', true)
                   ->whereIn('action', ['edit', 'manage'])
                   ->exists();
    }

    /**
     * Check if user can access specific broker type.
     */
    public function canAccessBrokerType(string $brokerType): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'broker_type')
                   ->where('resource_value', $brokerType)
                   ->where('is_active', true)
                   ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                   ->exists();
    }

    /**
     * Check if user can edit specific broker type.
     */
    public function canEditBrokerType(string $brokerType): bool
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'broker_type')
                   ->where('resource_value', $brokerType)
                   ->where('is_active', true)
                   ->whereIn('action', ['edit', 'manage'])
                   ->exists();
    }

    /**
     * Get all brokers this user can access.
     */
    public function getAccessibleBrokerIds(): array
    {
        $brokerIds = $this->resourcePermissions()
                         ->where('permission_type', 'broker')
                         ->where('is_active', true)
                         ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                         ->pluck('resource_id')
                         ->toArray();

        // If user has country permissions, we need to get broker IDs from those countries
        $countries = $this->resourcePermissions()
                         ->where('permission_type', 'country')
                         ->where('is_active', true)
                         ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                         ->pluck('resource_value')
                         ->toArray();

        // If user has zone permissions, we need to get broker IDs from those zones
        $zones = $this->resourcePermissions()
                     ->where('permission_type', 'zone')
                     ->where('is_active', true)
                     ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                     ->pluck('resource_value')
                     ->toArray();

        // If user has broker type permissions, we need to get broker IDs of those types
        $brokerTypes = $this->resourcePermissions()
                           ->where('permission_type', 'broker_type')
                           ->where('is_active', true)
                           ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                           ->pluck('resource_value')
                           ->toArray();

        // TODO: Implement queries to get broker IDs based on countries, zones, and broker types
        // This would require additional queries to the brokers table and related tables

        return array_unique($brokerIds);
    }

    /**
     * Get all countries this user can access.
     */
    public function getAccessibleCountries(): array
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'country')
                   ->where('is_active', true)
                   ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                   ->pluck('resource_value')
                   ->toArray();
    }

    /**
     * Get all zones this user can access.
     */
    public function getAccessibleZones(): array
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'zone')
                   ->where('is_active', true)
                   ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                   ->pluck('resource_value')
                   ->toArray();
    }

    /**
     * Get all broker types this user can access.
     */
    public function getAccessibleBrokerTypes(): array
    {
        return $this->resourcePermissions()
                   ->where('permission_type', 'broker_type')
                   ->where('is_active', true)
                   ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
                   ->pluck('resource_value')
                   ->toArray();
    }

    /**
     * Check if user has any resource permissions.
     */
    public function hasResourcePermissions(): bool
    {
        return $this->resourcePermissions()
                   ->where('is_active', true)
                   ->exists();
    }

    /**
     * Get resource permission summary.
     */
    public function getResourcePermissionSummary(): array
    {
        $permissions = $this->resourcePermissions()
                           ->where('is_active', true)
                           ->get()
                           ->groupBy('permission_type');

        return [
            'brokers' => $permissions->get('broker', collect())->pluck('resource_id')->toArray(),
            'countries' => $permissions->get('country', collect())->pluck('resource_value')->toArray(),
            'zones' => $permissions->get('zone', collect())->pluck('resource_value')->toArray(),
            'broker_types' => $permissions->get('broker_type', collect())->pluck('resource_value')->toArray(),
        ];
    }
}
