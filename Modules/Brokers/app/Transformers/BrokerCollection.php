<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BrokerCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        if($this->additional){
            return [
                'data' => $this->collection->map(function ($item) {
                    return (new BrokerResource($item))->additional($this->additional);
                }),
            ];
        }else 
        return parent::toArray($request);
        
    }
    public function __construct($resource, array $additional = [])
    {
        parent::__construct($resource);
        $this->additional = $additional;
    }
}
