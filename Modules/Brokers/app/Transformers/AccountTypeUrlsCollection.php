<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AccountTypeUrlsCollection extends ResourceCollection
{
    /**
     * @var class-string<AccountTypeUrlsResource>
     */
    public $collects = AccountTypeUrlsResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
