<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;
use App\Services\TranslateTrait;

class CompanyResource extends JsonResource
{

    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            //"id"=>$this->id,
           // "name" =>$this->name,
           // "licence_number"=>$this->licence_number,
          //  "banner"=>$this->banner,
         //  "description"=>$this->translateProp("description"),
           // "year_founded"=>$this->year_founded,
          //  "employees"=>$this->employees,
            "headquarters"=>$this->translateProp("headquarters"),
            "offices"=>$this->translateProp("offices"),
            //"translations"=>TranslationResource::collection($this->whenLoaded('translations'))
          ];
    }
}
