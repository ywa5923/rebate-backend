<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Auth\Models\MagicLink;
use Modules\Auth\Enums\AuthRole;

trait AuthUserTrait
{
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
     * Check if the user is a super admin.
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        $hasSuperAdminPermission = $this->resourcePermissions->where('permission_type', AuthRole::SUPER_ADMIN->value)->first() !== null;
        return $this->role === AuthRole::SUPER_ADMIN->value && $hasSuperAdminPermission;
    }

    /**
     * Get the Sanctum permissions for this user.
     * @return array
     */
    public function getSanctumPermissions(): array
    {
        if($this->isSuperAdmin()){
            return ['*'];
        }

        return $this->resourcePermissions
            ->filter(fn($p) => $p->is_active)
            ->map(function ($p) {
                return $p->permission_type === AuthRole::SUPER_ADMIN->value
                    ? [$p->permission_type]
                    : [$p->permission_type . ':' . ($p->action ?? '') . ':' . ($p->resource_id ?? '')];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get the permissions array for this user.
     * @return array
     */
    public function getPermissionsArray(): array
    {
        return $this->resourcePermissions
        ->filter(fn($p) => $p->is_active)
        ->map(function($permission) {
            return [
                'type' => $permission->permission_type,
                'resource_id' => $permission->resource_id??null,
                'resource_value' => $permission->resource_value??null,
                'action' => $permission->action,
            ];
        })->values()->toArray();
    }
}
