<?php

declare(strict_types=1);

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\DTOs\GroupedUrlsDTO;
use PHPUnit\Framework\TestCase;

class GroupedUrlsDTOTest extends TestCase
{
    public function test_it_holds_grouped_collections(): void
    {
        $byEntity = collect([
            1 => collect(['web' => collect([['id' => 1]])]),
        ]);
        $master = collect(['web' => collect([['id' => 2]])]);

        $dto = new GroupedUrlsDTO($byEntity, $master);

        $this->assertSame($byEntity, $dto->linksGroupedByEntityId);
        $this->assertSame($master, $dto->masterLinksGroupedByType);
    }
}
