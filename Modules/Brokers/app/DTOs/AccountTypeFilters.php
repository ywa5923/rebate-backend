<?php

namespace Modules\Brokers\DTOs;

final class AccountTypeFilters {
    public function __construct(
        public readonly BaseFilters $base,
        public readonly ?int $accountTypeId,
    ) {}

    public static function from(array $v): self {
        return new self(BaseFilters::from($v), isset($v['account_type_id']) ? (int)$v['account_type_id'] : null);
    }
}