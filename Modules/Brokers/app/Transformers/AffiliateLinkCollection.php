<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AffiliateLinkCollection extends ResourceCollection
{
    /**
     * @var class-string<AffiliateLinkResource>
     */
    public $collects = AffiliateLinkResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
