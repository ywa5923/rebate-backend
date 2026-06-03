<?php

namespace App\DTO;

use JsonSerializable;

final readonly class ApiData implements JsonSerializable
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $message = null,
        public mixed $errors = null,
        public ?PaginationMeta $pagination = null,
        public mixed $meta = null,
    ) {
    }

    public static function success(
        mixed $data = null,
        ?string $message = null,
        ?PaginationMeta $pagination = null,
        mixed $meta = null,
    ): self {
        return new self(
            success: true,
            data: $data,
            message: $message,
            pagination: $pagination,
            meta: $meta,
        );
    }

    public static function error(
        ?string $message = null,
        mixed $errors = null,
        mixed $data = null,
        mixed $meta = null,
    ): self {
        return new self(
            success: false,
            data: $data,
            message: $message,
            errors: $errors,
            meta: $meta,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
            'pagination' => $this->pagination?->toArray(),
            'meta' => $this->meta,
        ], static fn ($value) => $value !== null);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
