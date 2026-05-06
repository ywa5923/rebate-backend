<?php

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\DTOs\ListDTO;
use Modules\Brokers\DTOs\ListItemDTO;
use Modules\Brokers\DTOs\StoreAffiliateLinkDTO;
use PHPUnit\Framework\TestCase;

class StoreAffiliateLinkDTOTest extends TestCase
{
    public function test_from_validated_maps_required_and_optional_fields(): void
    {
        $dto = StoreAffiliateLinkDTO::fromValidated([
            'url_type' => 'ib-affiliate-link',
            'name' => 'My link',
            'url' => 'https://example.com/aff',
            'account_type_id' => null,
            'zone_id' => 2,
            'is_master_link' => true,
            'platform_urls' => [['id' => 1, 'name' => 'Web']],
            'currency' => 'USD',
        ]);

        $this->assertSame('ib-affiliate-link', $dto->urlType);
        $this->assertSame('My link', $dto->name);
        $this->assertSame('https://example.com/aff', $dto->url);
        $this->assertNull($dto->accountTypeId);
        $this->assertSame(2, $dto->zoneId);
        $this->assertTrue($dto->isMasterLink);
        $this->assertInstanceOf(ListDTO::class, $dto->platformUrls);
        $this->assertCount(1, $dto->platformUrls);
        $this->assertInstanceOf(ListItemDTO::class, $dto->platformUrls->items[0]);
        $this->assertSame(1, $dto->platformUrls->items[0]->id);
        $this->assertSame('Web', $dto->platformUrls->items[0]->name);
        $this->assertSame('USD', $dto->currency);
    }

    public function test_from_validated_defaults_optional_arrays_and_flags(): void
    {
        $dto = StoreAffiliateLinkDTO::fromValidated([
            'url_type' => 'ib-affiliate-link',
            'name' => 'My link',
            'url' => 'https://example.com/aff',
        ]);

        $this->assertNull($dto->accountTypeId);
        $this->assertNull($dto->zoneId);
        $this->assertFalse($dto->isMasterLink);
        $this->assertCount(0, $dto->platformUrls);
        $this->assertSame([], $dto->platformUrls->items);
        $this->assertNull($dto->currency);
    }
}
