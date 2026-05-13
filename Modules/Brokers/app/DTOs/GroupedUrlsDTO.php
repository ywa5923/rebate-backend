<?php

declare(strict_types=1);

namespace Modules\Brokers\DTOs;

use Illuminate\Support\Collection;

/**
 * Nested {@see \Illuminate\Support\Collection} structures produced by
 * {@see \Modules\Brokers\Transformers\URLResource::collection()} plus
 * {@see \Illuminate\Support\Collection::groupBy()}; leaf values are URL payload arrays.
 */
final readonly class GroupedUrlsDTO
{
    public function __construct(
        public Collection $linksGroupedByEntityId,
        public Collection $masterLinksGroupedByType,
    ) {
    }
}
