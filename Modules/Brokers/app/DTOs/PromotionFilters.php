<?php

namespace Modules\Brokers\DTOs;
use Modules\Brokers\DTOs\BaseFilters;

final class PromotionFilters
{
    public function __construct(
        public readonly BaseFilters $base,
        public readonly ?int $promotionId,
    ) {
    }

    public static function from(array $v): self
    {
        return new self(BaseFilters::from($v), isset($v['promotion_id']) ? (int) $v['promotion_id'] : null);
    }
}
