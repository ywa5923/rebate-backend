<?php

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\Transformers\AffiliateLinkCollection;
use Modules\Brokers\Transformers\AffiliateLinkResource;
use PHPUnit\Framework\TestCase;

class AffiliateLinkCollectionTest extends TestCase
{
    public function test_it_collects_affiliate_link_resources(): void
    {
        $collection = new AffiliateLinkCollection(collect());

        $this->assertSame(AffiliateLinkResource::class, $collection->collects);
    }
}
