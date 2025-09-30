<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;
use App\Utilities\TranslateTrait;

class CompanyResource extends JsonResource
{

    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "option_values" => OptionValueResource::collection($this->whenLoaded('optionValues')),
        ];
    }
}
