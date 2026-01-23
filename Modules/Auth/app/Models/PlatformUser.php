<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Auth\Models\MagicLink;
use Modules\Auth\Models\UserPermission;

class PlatformUser extends Authenticatable
{
    use Notifiable, HasApiTokens, AuthUserTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Deprecated: Get magic links for this platform user.
     */
    public function magicLinksOld(): HasMany
    {
        return $this->hasMany(MagicLink::class, 'subject_id')
                    ->where('subject_type', 'Modules\\Auth\\Models\\PlatformUser');
    }

    // /**
    //  * Get magic links for this platform user.
    //  * @return MorphMany
    //  * @throws \Exception
    //  */
    // public function magicLinks(): MorphMany
    // {
    //     return $this->morphMany(MagicLink::class, 'subject');
    // }

    /**
     * Get resource permissions for this platform user.
     */
    public function resourcePermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class, 'subject_id')
                    ->where('subject_type', 'Modules\\Auth\\Models\\PlatformUser');
    }

    // /**
    //  * Check if user can access specific broker.
    //  */
    // public function canAccessBroker(int $brokerId): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'broker')
    //                ->where('resource_id', $brokerId)
    //                ->where('is_active', true)
    //                ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
    //                ->exists();
    // }

    // /**
    //  * Check if user can edit specific broker.
    //  */
    // public function canEditBroker(int $brokerId): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'broker')
    //                ->where('resource_id', $brokerId)
    //                ->where('is_active', true)
    //                ->whereIn('action', ['edit', 'manage'])
    //                ->exists();
    // }

    // /**
    //  * Check if user can manage specific broker.
    //  */
    // public function canManageBroker(int $brokerId): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'broker')
    //                ->where('resource_id', $brokerId)
    //                ->where('is_active', true)
    //                ->where('action', 'manage')
    //                ->exists();
    // }

    // /**
    //  * Check if user can access specific country.
    //  */
    // public function canAccessCountry(string $country): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'country')
    //                ->where('resource_value', $country)
    //                ->where('is_active', true)
    //                ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
    //                ->exists();
    // }

    // /**
    //  * Check if user can edit specific country.
    //  */
    // public function canEditCountry(string $country): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'country')
    //                ->where('resource_value', $country)
    //                ->where('is_active', true)
    //                ->whereIn('action', ['edit', 'manage'])
    //                ->exists();
    // }

    // /**
    //  * Check if user can access specific zone.
    //  */
    // public function canAccessZone(string $zone): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'zone')
    //                ->where('resource_value', $zone)
    //                ->where('is_active', true)
    //                ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
    //                ->exists();
    // }

    // /**
    //  * Check if user can edit specific zone.
    //  */
    // public function canEditZone(string $zone): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'zone')
    //                ->where('resource_value', $zone)
    //                ->where('is_active', true)
    //                ->whereIn('action', ['edit', 'manage'])
    //                ->exists();
    // }

    // /**
    //  * Check if user can access specific broker type.
    //  */
    // public function canAccessBrokerType(string $brokerType): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'broker_type')
    //                ->where('resource_value', $brokerType)
    //                ->where('is_active', true)
    //                ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
    //                ->exists();
    // }

    // /**
    //  * Check if user can edit specific broker type.
    //  */
    // public function canEditBrokerType(string $brokerType): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'broker_type')
    //                ->where('resource_value', $brokerType)
    //                ->where('is_active', true)
    //                ->whereIn('action', ['edit', 'manage'])
    //                ->exists();
    // }

    // /**
    //  * Get all accessible broker IDs.
    //  */
    // public function getAccessibleBrokerIds(): array
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'broker')
    //                ->where('is_active', true)
    //                ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
    //                ->pluck('resource_id')
    //                ->toArray();
    // }

    // /**
    //  * Get all accessible countries.
    //  */
    // public function getAccessibleCountries(): array
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'country')
    //                ->where('is_active', true)
    //                ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
    //                ->pluck('resource_value')
    //                ->toArray();
    // }

    // /**
    //  * Get all accessible zones.
    //  */
    // public function getAccessibleZones(): array
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'zone')
    //                ->where('is_active', true)
    //                ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
    //                ->pluck('resource_value')
    //                ->toArray();
    // }

    // /**
    //  * Get all accessible broker types.
    //  */
    // public function getAccessibleBrokerTypes(): array
    // {
    //     return $this->resourcePermissions()
    //                ->where('permission_type', 'broker_type')
    //                ->where('is_active', true)
    //                ->whereIn('action', ['view', 'edit', 'delete', 'manage'])
    //                ->pluck('resource_value')
    //                ->toArray();
    // }

    // /**
    //  * Get permission summary.
    //  */
    // public function getResourcePermissionSummary(): array
    // {
    //     return [
    //         'brokers' => $this->getAccessibleBrokerIds(),
    //         'countries' => $this->getAccessibleCountries(),
    //         'zones' => $this->getAccessibleZones(),
    //         'broker_types' => $this->getAccessibleBrokerTypes(),
    //     ];
    // }

    // /**
    //  * Check if user has any resource permissions.
    //  */
    // public function hasResourcePermissions(): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('is_active', true)
    //                ->exists();
    // }
}