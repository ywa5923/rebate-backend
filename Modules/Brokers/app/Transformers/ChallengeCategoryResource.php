<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;
use App\Utilities\TranslateTrait;

/**
 * @OA\Schema(
 *   schema="ChallengeCategory",
 *   type="object",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="name", type="string", example="Trading Challenge"),
 *   @OA\Property(property="description", type="string", example="Challenge description", nullable=true),
 *   @OA\Property(property="is_active", type="boolean", example=true),
 *   @OA\Property(property="steps", type="array", @OA\Items(ref="#/components/schemas/ChallengeStep"), nullable=true),
 *   @OA\Property(property="amounts", type="array", @OA\Items(ref="#/components/schemas/ChallengeAmount"), nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ChallengeCategoryResource extends JsonResource
{
    use TranslateTrait;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
           
            
            // Relationships
            'steps' => ChallengeStepResource::collection($this->whenLoaded('steps')),
            'amounts' => ChallengeAmountResource::collection($this->whenLoaded('amounts')),
            
            // Translations (if loaded)
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            
            // Timestamps
           // 'created_at' => $this->created_at,
           // 'updated_at' => $this->updated_at,
        ];
    }
}
