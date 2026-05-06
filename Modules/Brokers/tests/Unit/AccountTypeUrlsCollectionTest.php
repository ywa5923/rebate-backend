<?php

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\Transformers\AccountTypeUrlsCollection;
use Modules\Brokers\Transformers\AccountTypeUrlsResource;
use PHPUnit\Framework\TestCase;

class AccountTypeUrlsCollectionTest extends TestCase
{
    public function test_it_collects_account_type_urls_resources(): void
    {
        $collection = new AccountTypeUrlsCollection(collect());

        $this->assertSame(AccountTypeUrlsResource::class, $collection->collects);
    }
}
