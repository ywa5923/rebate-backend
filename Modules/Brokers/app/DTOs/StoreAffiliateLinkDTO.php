<?php

declare(strict_types=1);

namespace Modules\Brokers\DTOs;

final readonly class StoreAffiliateLinkDTO
{
    public function __construct(
        public string $urlType,
        public string $name,
        public string $url,
        public ?int $accountTypeId,
        public ?int $zoneId,
        public bool $isMasterLink,
        public ListDTO $platformUrls,
        public ?string $currency,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {

        $platformUrls = ListDTO::fromValidatedRows($data['platform_urls'] ?? []);

        return new self(
            urlType: $data['url_type'],
            name: $data['name'],
            url: $data['url'],
            accountTypeId: $data['account_type_id'] ?? null,
            zoneId: $data['zone_id'] ?? null,
            isMasterLink: (bool) ($data['is_master_link'] ?? false),
            platformUrls: $platformUrls,
            currency: $data['currency'] ?? null,
        );
    }
}
