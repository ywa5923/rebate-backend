<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\TranslateTrait;

class RegualtorResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            //"id"=>$this->id,
            "abreviation" => $this->abreviation,
            "country" => $this->translateProp("country")
        ];
    }
}
