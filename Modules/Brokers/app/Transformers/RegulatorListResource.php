<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;
//use Modules\Translations\Transformers\TranslationResource;
class RegulatorListResource extends JsonResource
{
    use TranslateTrait;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name."(".$this->acronym.")-".$this->translateProp("country"),
            //'translations' => TranslationResource::collection($this->whenLoaded('translations')),
        ];
    }
}