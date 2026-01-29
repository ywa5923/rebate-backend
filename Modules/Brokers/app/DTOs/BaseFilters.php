<?php

namespace Modules\Brokers\DTOs;

final class BaseFilters {
    public const SORT_DIRECTION = 'asc';
    public const PER_PAGE = 15;
    public const PAGE = 1;

    public function __construct(
        public readonly ?string $zoneCode,
        public readonly ?string $languageCode,
        public readonly ?string $sortBy,
        public readonly string $sortDirection,
        public readonly int $perPage,
        public readonly int $page,
    ) {}

    public static function from(array $v): self {
        return new self(
            zoneCode: $v['zone_code'] ?? null,
            languageCode: $v['language_code'] ?? null,
            sortBy: $v['sort_by'] ?? null,
            sortDirection: strtolower($v['sort_direction'] ?? self::SORT_DIRECTION),
            perPage: (int)($v['per_page'] ?? self::PER_PAGE),
            page: (int)($v['page'] ?? self::PAGE),
        );
    }
}