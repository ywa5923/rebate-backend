<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatrixDimensionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'order' => $this->order,
            'matrix_id' => $this->matrix_id,
            'matrix_header_id' => $this->matrix_header_id,
            'broker_id' => $this->broker_id,
            'zone_id' => $this->zone_id,
            'is_invariant' => $this->is_invariant,
            'matrixHeader' => new  MatrixHeaderResource($this->matrixHeader),
            'matrixRowCells' => $this->whenLoaded('matrixRowCells', function () {
                return MatrixValueResource::collection($this->matrixRowCells);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
