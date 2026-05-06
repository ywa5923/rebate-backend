<?php

declare(strict_types=1);

namespace Modules\Brokers\DTOs;

final readonly class ListItemDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromValidatedArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            name: (string) $row['name'],
        );
    }
}
