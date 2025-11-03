<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BrokerOptionCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = BrokerOptionResource::class;

    /**
     * Create a new resource collection instance.
     *
     * @param  mixed  $resource
     * @param  array  $additional
     * @return void
     */
    public function __construct($resource, array $additional = [])
    {
        parent::__construct($resource);
        $this->additional = $additional;
    }

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        if (!empty($this->additional)) {
             
            return  $this->collection->map(function ($item) use ($request) {
                    return (new BrokerOptionResource($item))->additional($this->additional)->toArray($request);
                })->all();
            
        }
        
        return parent::toArray($request);
    }
}
