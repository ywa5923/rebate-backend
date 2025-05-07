<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Brokers\Transformers\MatrixDimensionResource;
class MatrixDimensionCollection extends ResourceCollection
{
    public $collects = MatrixDimensionResource::class;
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
