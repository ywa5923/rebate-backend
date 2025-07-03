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
            "name" => $this->name,
            "name_p" => $this->name_p,
            "licence_number" => $this->licence_number,
            "licence_number_p" => $this->licence_number_p,
            "banner" => $this->banner,
            "banner_p" => $this->banner_p,
            "description" => $this->translateProp("description"),
            "description_p" => $this->description_p,
            "year_founded" => $this->year_founded,
            "year_founded_p" => $this->year_founded_p,
            "employees" => $this->employees,
            "employees_p" => $this->employees_p,
            "headquarters" => $this->headquarters,
            "headquarters_p" => $this->translateProp('headquarters_p'),
            "offices" => $this->offices,
            "offices_p" => $this->translateProp('offices_p'),
            "status" => $this->status,
            "status_reason" => $this->status_reason,
            "brokers" => $this->whenLoaded('brokers'),
            "translations" => TranslationResource::collection($this->whenLoaded('translations')),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
