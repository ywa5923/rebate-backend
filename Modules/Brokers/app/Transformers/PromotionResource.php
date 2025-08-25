<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;
use App\Utilities\TranslateTrait;
use Modules\Brokers\Transformers\OptionValueResource;

/**
 * @OA\Schema(
 *   schema="Promotion",
 *   type="object",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="broker_id", type="integer", example=1),
 * )
 */
class PromotionResource extends JsonResource
{
    use TranslateTrait;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'broker_id' => $this->broker_id,
            'option_values' => OptionValueResource::collection($this->whenLoaded('optionValues')),
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 