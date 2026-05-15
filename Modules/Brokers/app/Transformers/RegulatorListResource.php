<?php

namespace Modules\Brokers\Transformers;

use App\Utilities\TranslateTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//use Modules\Translations\Transformers\TranslationResource;
class RegulatorListResource extends JsonResource
{
    use TranslateTrait;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name.'('.$this->acronym.')-'.$this->translateProp('country').' '.$this->rating.' stars',
            //'translations' => TranslationResource::collection($this->whenLoaded('translations')),
        ];
    }
}
