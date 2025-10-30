<?php

namespace Modules\Auth\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
           
            'last_login_at' => $this->last_login_at?->toISOString(),
            'permissions' => $this->whenLoaded('resourcePermissions', function () {
                return $this->resourcePermissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'type' => $permission->permission_type,
                        'resource_id' => $permission->resource_id,
                        'resource_value' => $permission->resource_value,
                        'action' => $permission->action,
                        'is_active' => $permission->is_active,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

