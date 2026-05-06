<?php

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\DTOs\ListItemDTO;
use PHPUnit\Framework\TestCase;

class ListItemDTOTest extends TestCase
{
    public function test_from_validated_array(): void
    {
        $item = ListItemDTO::fromValidatedArray([
            'id' => 10,
            'name' => 'Web platform',
        ]);

        $this->assertSame(10, $item->id);
        $this->assertSame('Web platform', $item->name);
    }
}
