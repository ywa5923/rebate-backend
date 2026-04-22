<?php

namespace Modules\Brokers\Transformers;

use App\Utilities\TranslateTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssociatedUrlResource extends JsonResource
{
    use TranslateTrait;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        $result = [
            // Basic Information
            'id' => $this->id,

            'url' => $this->url,
            'public_url' => $this->public_url,

            'name' => $this->name,
            'public_name' => $this->public_name,

            'is_updated_entry' => $this->pivot->is_updated_entry,

            'is_public' => $this->pivot->is_public,

        ];

        return $result;
    }
}
