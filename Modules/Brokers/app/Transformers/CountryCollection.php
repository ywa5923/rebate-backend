<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CountryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        if (!empty($this->additional)) {
            return $this->collection->map(function ($item) use ($request) {
                return (new CountryResource($item))->additional($this->additional)->resolve($request);
            })->toArray();
        }
        
        return parent::toArray($request);
    }
    
    public function __construct($resource, array $additional = [])
    {
        parent::__construct($resource);
        $this->additional = $additional;
    }
}

