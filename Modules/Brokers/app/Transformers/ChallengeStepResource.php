<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Translations\Transformers\TranslationResource;
use App\Utilities\TranslateTrait;

/**
 * @OA\Schema(
 *   schema="ChallengeStep",
 *   type="object",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="challenge_category_id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Step 1"),
 *   @OA\Property(property="description", type="string", example="First step description", nullable=true),
 *   @OA\Property(property="order", type="integer", example=1),
 *   @OA\Property(property="is_active", type="boolean", example=true),
 *   @OA\Property(property="challenge_category", type="object", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ChallengeStepResource extends JsonResource
{
    use TranslateTrait;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            //'challenge_category_id' => $this->challenge_category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            // Relationships
            // 'challenge_category' => $this->whenLoaded('challengeCategory', function () {
            //     return [
            //         'id' => $this->challengeCategory->id,
            //         'name' => $this->challengeCategory->name,
            //     ];
            // }),
            
            // Translations (if loaded)
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            
            // Timestamps
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
        ];
    }
}
