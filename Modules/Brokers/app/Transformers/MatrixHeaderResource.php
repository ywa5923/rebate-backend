<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatrixHeaderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = [

            'slug' => $this->slug,
            'name' => $this->translateProp('title'),
            'is_percentage' => (bool) $this->is_percentage,
            'broker_can_see' => $this->broker_can_see,
            'description' => $this->description,
        ];

        if ($this->type == 'row') {
            $data['options'] = $this->children->map(function ($child) {
                return [
                    'value' => $child->slug,
                    'label' => $child->title,
                ];
            });
        }

        if ($this->formType) {
            $data['form_type'] = new FormTypeResource($this->formType);
        }

        return $data;
    }
}
