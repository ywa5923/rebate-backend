<?php

namespace App\DTO;

use Illuminate\Pagination\LengthAwarePaginator;

final class PaginationMeta
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $lastPage,
        public readonly int $perPage,
        public readonly int $total,
    ) {
    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
        );
    }

    public static function fromPaginatorOrNull(mixed $result): ?self
    {
        return $result instanceof LengthAwarePaginator ? self::fromPaginator($result) : null;
    }

    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
        ];
    }
}
