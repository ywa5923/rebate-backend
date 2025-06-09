<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'placeholder' => $this->placeholder,
        ];

        if (in_array($this->type, ['single-select', 'multi-select']) && $this->dropdown) {
            $data['options'] = $this->dropdown->dropdownOptions->map(fn($option) => [
                'value' => $option->value,
                'label' => $option->label
            ]);
        }

        return $data;
    }
}
