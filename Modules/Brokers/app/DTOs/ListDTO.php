<?php

declare(strict_types=1);

namespace Modules\Brokers\DTOs;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, ListItemDTO>
 */
final readonly class ListDTO implements Countable, IteratorAggregate
{
    /**
     * @param  list<ListItemDTO>  $items
     */
    public function __construct(
        public array $items,
    ) {
    }

    /**
     * @param  list<array{id: int, name: string}>  $rows
     */
    public static function fromValidatedRows(array $rows): self
    {
        $items = array_values(array_map(
            static fn (array $row): ListItemDTO => ListItemDTO::fromValidatedArray($row),
            $rows,
        ));

        return new self($items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
