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
    use Notifiable, HasApiTokens, AuthUserTrait;

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

    // /**
    //  * Deprecated: Get magic links for this user.
    //  */
    // public function magicLinksBuilder(): HasMany
    // {
    //     return $this->hasMany(MagicLink::class, 'subject_id')
    //                 ->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser');
    // }

    // /**
    //  * Get magic links for this user.
    //  * @return MorphMany
    //  * @throws \Exception
    //  */
    // public function magicLinks(): MorphMany
    // {
    //     return $this->morphMany(MagicLink::class, 'subject');
    // }

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
    // public function hasPermission(string $permission): bool
    // {
    //     // Check user-specific permissions first
    //     $userPermissions = $this->permissions ?? [];
    //     if (in_array($permission, $userPermissions) || in_array('*', $userPermissions)) {
    //         return true;
    //     }

    //     // Check team permissions
    //     return $this->team?->hasPermission($permission) ?? false;
    // }

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

    // /**
    //  * Add permission to user.
    //  */
    // public function addPermission(string $permission): void
    // {
    //     $permissions = $this->permissions ?? [];
    //     if (!in_array($permission, $permissions)) {
    //         $permissions[] = $permission;
    //         $this->update(['permissions' => $permissions]);
    //     }
    // }

    // /**
    //  * Remove permission from user.
    //  */
    // public function removePermission(string $permission): void
    // {
    //     $permissions = $this->permissions ?? [];
    //     $permissions = array_filter($permissions, fn($p) => $p !== $permission);
    //     $this->update(['permissions' => array_values($permissions)]);
    // }

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
    // public function scopeActive($query)
    // {
    //     return $query->where('is_active', true);
    // }

    

    // ==================== RESOURCE PERMISSION METHODS ====================

    /**
     * Check if user can access specific broker.
     */
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
    //  * Check if user has any resource permissions.
    //  */
    // public function hasResourcePermissions(): bool
    // {
    //     return $this->resourcePermissions()
    //                ->where('is_active', true)
    //                ->exists();
    // }

   
}
