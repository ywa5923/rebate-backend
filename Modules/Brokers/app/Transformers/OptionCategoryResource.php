<?php
namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;
use Modules\Brokers\Transformers\FormBrokerOptionResource;
class OptionCategoryResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "name"=>$this->translate("name"),
            "description"=>$this->translate("description"),
            "default_language"=>$this->default_language,
            "position"=>$this->position,
            "options"=>FormBrokerOptionResource::collection($this->options),
        ];
    }
}
