<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utilities\TranslateTrait;
class MatrixValueResource extends JsonResource
{
    
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matrix_row_id' => $this->matrix_row_id,
            'matrix_column_id' => $this->matrix_column_id,
            'broker_id' => $this->broker_id,
            'matrix_id' => $this->matrix_id,
            "zone_id" => $this->zone_id,
            "is_invariant" => $this->is_invariant,
            'value' => $this->translateProp('value'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
