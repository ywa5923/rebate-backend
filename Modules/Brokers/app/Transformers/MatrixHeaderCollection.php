<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Brokers\Transformers\MatrixHeaderResource;

class MatrixHeaderCollection extends ResourceCollection
{
    public $collects = MatrixHeaderResource::class; 
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
