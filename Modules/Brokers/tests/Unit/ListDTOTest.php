<?php

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\DTOs\ListDTO;
use Modules\Brokers\DTOs\ListItemDTO;
use PHPUnit\Framework\TestCase;

class ListDTOTest extends TestCase
{
    public function test_from_validated_rows_and_iteration(): void
    {
        $list = ListDTO::fromValidatedRows([
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);

        $this->assertCount(2, $list);
        $ids = [];
        foreach ($list as $item) {
            $this->assertInstanceOf(ListItemDTO::class, $item);
            $ids[] = $item->id;
        }
        $this->assertSame([1, 2], $ids);
    }

    public function test_empty_list(): void
    {
        $list = ListDTO::fromValidatedRows([]);

        $this->assertCount(0, $list);
        $this->assertSame([], $list->items);
    }
}
