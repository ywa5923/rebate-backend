<?php

namespace Modules\Brokers\Tests\Unit;

use Mockery;
use Modules\Brokers\Models\AffliliateLink;
use Modules\Brokers\Repositories\AffiliateLinkRepository;
use PHPUnit\Framework\TestCase;

class AffiliateLinkRepositoryUpdateTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_applies_attributes_and_refreshes_model(): void
    {
        $constructorModel = Mockery::mock(AffliliateLink::class);

        $record = Mockery::mock(AffliliateLink::class);
        $record->shouldReceive('update')->once()->with(['name' => 'updated']);
        $record->shouldReceive('refresh')->once()->andReturn($record);

        $repository = new AffiliateLinkRepository($constructorModel);
        $result = $repository->update($record, ['name' => 'updated']);

        $this->assertSame($record, $result);
    }
}
