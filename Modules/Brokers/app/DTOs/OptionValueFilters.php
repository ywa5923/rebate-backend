<?php

namespace Modules\Brokers\DTOs;

class OptionValueFilters
{
    public function __construct(
        public readonly BaseFilters $base,
        public readonly ?string $entityType,
        public readonly ?int $entityId,
        public readonly ?int $brokerOptionId,
        public readonly ?int $categoryId,
        public readonly ?string $optionSlug,
        public readonly ?string $visibileFor,
        public readonly ?string $search,
        public readonly bool $shouldPaginate,
    ) {
    }

    public static function from(array $v): self
    {
        return new self(
            base: BaseFilters::from($v),
            entityType: $v['entity_type'] ?? null,
            entityId: ($v['entity_id'] ?? null) !== null ? (int) $v['entity_id'] : null,
            brokerOptionId: ($v['broker_option_id'] ?? null) !== null ? (int) $v['broker_option_id'] : null,
            categoryId: ($v['category_id'] ?? null) !== null ? (int) $v['category_id'] : null,
            optionSlug: $v['option_slug'] ?? null,
            visibileFor: $v['visibile_for'] ?? null,
            search: $v['search'] ?? null,
            shouldPaginate: isset($v['per_page']) || isset($v['page']),
        );
    }
}
