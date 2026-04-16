<?php
namespace Modules\Brokers\Transformers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use App\Utilities\TranslateTrait;
use Modules\Brokers\Transformers\URLResource;
use Modules\Brokers\Enums\UrlTypeEnum;
use Modules\Brokers\Models\Url;
class AccountTypeUrlsResource extends JsonResource
{
    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        //$urls = URLResource::collection($this->whenLoaded('urls'));
     
        $platformUrls = $this->urls->where('url_type', UrlTypeEnum::WEBPLATFORM->value)->values()->map(
            fn (Url $url) => Arr::only($url->toArray($request), ['id', 'name'])
        );
        return [
            "account_type_id" => $this->id,
            // Relationships
            //"broker_id" => $this->broker_id,
            'account_type_name' => optional(OptionValueResource::collection($this->whenLoaded('optionValues'))[0])->value ?? 'unknown',
            "platform_urls" => $platformUrls
        ];
    }
}
