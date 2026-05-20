<?php

namespace Modules\Brokers\DTOs;

final class ContestFilters
{
    public function __construct(
        public readonly BaseFilters $base,
        public readonly ?int $contestId,
    ) {
    }

    public static function from(array $v): self
    {
        return new self(BaseFilters::from($v), isset($v['contest_id']) ? (int) $v['contest_id'] : null);
    }
}
