<?php

namespace Modules\Translations\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LocaleResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
       
        //return parent::toArray($request);
        return $this->collection->reduce(function ($carry, $item) use ($request) {
            return array_merge($carry, $item->toArray($request)); // Pass $request here
        }, []);
        
    }
}

