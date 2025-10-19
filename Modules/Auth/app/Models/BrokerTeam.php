<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Brokers\Models\Broker;

class BrokerTeam extends Model
{
    protected $fillable = [
        'broker_id',
        'name',
        'description',
        'is_active',
        'permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    /**
     * Get the broker that owns the team.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    /**
     * Get the team users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(BrokerTeamUser::class);
    }

    /**
     * Get active team users.
     */
    public function activeUsers(): HasMany
    {
        return $this->hasMany(BrokerTeamUser::class)->where('is_active', true);
    }

    /**
     * Check if team has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    /**
     * Add permission to team.
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
     * Remove permission from team.
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Scope for active teams.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
