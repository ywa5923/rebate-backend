<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPermission extends Model
{
    use HasFactory;

    protected $table = 'user_permissions';

    protected $fillable = [
        'subject_type',
        'subject_id',
        'permission_type',
        'resource_id',
        'resource_value',
        'action',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the team user that owns the permission.
     */
    public function teamUser(): BelongsTo
    {
        return $this->belongsTo(BrokerTeamUser::class);
    }

    /**
     * Get the broker through the team user.
     */
    public function broker(): BelongsTo
    {
        return $this->teamUser->team->broker();
    }

    /**
     * Polymorphic subject (team user, platform user, etc.).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo(null, 'subject_type', 'subject_id');
    }

    /**
     * Scope for active permissions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific permission type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('permission_type', $type);
    }

    /**
     * Scope for specific action.
     */
    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific resource.
     */
    public function scopeForResource($query, string $type, $resourceId = null, $resourceValue = null)
    {
        return $query->where('permission_type', $type)
                    ->where(function ($q) use ($resourceId, $resourceValue) {
                        if ($resourceId !== null) {
                            $q->where('resource_id', $resourceId);
                        }
                        if ($resourceValue !== null) {
                            $q->orWhere('resource_value', $resourceValue);
                        }
                    });
    }

    /**
     * Check if permission allows specific action.
     */
    public function allowsAction(string $action): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Manage action allows all other actions
        if ($this->action === 'manage') {
            return true;
        }

        return $this->action === $action;
    }

    /**
     * Check if permission is for specific resource.
     */
    public function isForResource(string $type, $resourceId = null, $resourceValue = null): bool
    {
        if ($this->permission_type !== $type) {
            return false;
        }

        if ($resourceId !== null && $this->resource_id === $resourceId) {
            return true;
        }

        if ($resourceValue !== null && $this->resource_value === $resourceValue) {
            return true;
        }

        return false;
    }
}
