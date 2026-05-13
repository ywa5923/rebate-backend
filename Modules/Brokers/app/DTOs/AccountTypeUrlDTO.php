<?php

namespace Modules\Brokers\DTOs;

class AccountTypeUrlDTO
{
    public function __construct(
        public ?int $id,
        public string $url_type,
        public string $url,
        public string $name,
        public ?int $account_type_id,
        public int $broker_id,
        public ?int $zone_id,
    ) {
    }

    public static function fromValidated(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['url_type'],
            $data['url'],
            $data['name'],
            $data['account_type_id'] ?? null,
            $data['broker_id'],
            $data['zone_id'] ?? null,
        );
    }
}
