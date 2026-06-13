<?php

namespace Modules\Brokers\DTOs;
use Carbon\Carbon;


class ChallengeMatrixCellDTO
{
    public function __construct(
      
        public int $id,
        public string $rowSlug,
        public string $colSlug,
        public ?string $value,
        public ?string $previousValue,
        public ?string $publicValue,
        public bool $isUpdatedEntry,
        public ?int $zoneId,
        public int $rowId,
        public int $columnId,
        public ?Carbon $createdAt,
        public ?Carbon $updatedAt,
    ) {
    }

    public static function fromValidated(array $data): self
    {
        return new self(
            $data['id'],
            $data['row_slug'],
            $data['col_slug'],
            $data['value'] ?? null,
            $data['previous_value'] ?? null,
            $data['public_value'] ?? null,
            $data['is_updated_entry'] ?? false,
            $data['zone_id'] ?? null,
            $data['row_id'] ?? null,
            $data['column_id'] ?? null,
            $data['created_at'] ? Carbon::parse($data['created_at']) : null,
            $data['updated_at'] ? Carbon::parse($data['updated_at']) : null,
        );
    }
}


