<?php

namespace Modules\Translations\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocalesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $flagUrl = $this->flag_path 
            ? config('services.cloudflare.public_url') . '/' . $this->flag_path 
            : null;

        return [
            'id' => $this->id,
            'country' => $this->country,
            'code' => $this->code,
            'description' => $this->description,
            'flag_path' => $this->flag_path,
            'flag_url' => $flagUrl
        ];
    }
}
