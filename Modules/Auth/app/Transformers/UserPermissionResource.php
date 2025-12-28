<?php

namespace Modules\Auth\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionResource extends JsonResource
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
            'subject_type' => $this->subject_type ? class_basename($this->subject_type) : null,
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', function () {
                // return [
                //     'id' => $this->subject->id,
                //     'name' => $this->subject->name,
                //     'email' => $this->subject->email,
                // ];
                return $this->subject->name." (".$this->subject->email.")";
            }),
            'permission_type' => $this->permission_type,
            'resource_id' => $this->resource_id,
            'resource_value' => $this->resource_value,
            'action' => $this->action,
            'metadata' => $this->metadata,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

