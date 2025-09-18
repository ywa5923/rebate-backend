<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;

/**
 * @OA\Schema(
 *   schema="OptionValueResource",
 *   type="object",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="option_slug", type="string", example="minimum_deposit"),
 *   @OA\Property(property="value", type="string", example="100"),
 *   @OA\Property(property="public_value", type="string", example="$100", nullable=true),
 *   @OA\Property(property="status", type="boolean", example=true),
 *   @OA\Property(property="status_message", type="string", example="Active option", nullable=true),
 *   @OA\Property(property="default_loading", type="boolean", example=true),
 *   @OA\Property(property="type", type="string", example="number", nullable=true),
 *   @OA\Property(property="metadata", type="object", nullable=true),
 *   @OA\Property(property="is_invariant", type="boolean", example=true),
 *   @OA\Property(property="delete_by_system", type="boolean", example=false),
 *   @OA\Property(property="broker_id", type="integer", example=1),
 *   @OA\Property(property="broker_option_id", type="integer", example=1),
 *   @OA\Property(property="zone_id", type="integer", example=1, nullable=true),
 *   @OA\Property(property="broker", type="object", nullable=true),
 *   @OA\Property(property="option", type="object", nullable=true),
 *   @OA\Property(property="zone", type="object", nullable=true),
 *   @OA\Property(property="translations", type="array", @OA\Items(type="object"), nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OptionValueResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'option_slug' => $this->option_slug,
            'previous_value' => $this->previous_value,
            'is_updated_entry' => $this->is_updated_entry,
            "value" => $this->value,
            'public_value' => $this->translateOptionPublicValue($this->option_slug),
            'status' => $this->status,
            'status_message' => $this->status_message,
            'default_loading' => $this->default_loading,
            'type' => $this->type,
            'metadata' => $this->whenNotNull($this->translateOptionMeta($this->option_slug)),
            'is_invariant' => $this->is_invariant,
            'delete_by_system' => $this->delete_by_system,
            'broker_id' => $this->broker_id,
            'broker_option_id' => $this->broker_option_id,
            'zone_id' => $this->zone_id,
            'zone_code' => $this->zone_code,
            'optionable_type' => $this->optionable_type,
            'optionable_id' => $this->optionable_id,
            'applicable_for' => $this->applicable_for,
            //'broker' => $this->whenLoaded('broker'),
            //'option' => $this->whenLoaded('option'),
           // 'zone' => $this->whenLoaded('zone'),
            //'translations' => $this->whenLoaded('translations'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 