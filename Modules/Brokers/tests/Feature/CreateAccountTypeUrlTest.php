<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\DTOs\AccountTypeUrlDTO;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Services\UrlService;
use Tests\TestCase;

class CreateAccountTypeUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_account_type_url_returns_url_model(): void
    {
        $brokerType = BrokerType::query()->create([
            'name' => 'Broker',
        ]);

        $broker = Broker::query()->create([
            'broker_type_id' => $brokerType->id,
        ]);

        $dto = new AccountTypeUrlDTO(
            id: null,
            url_type: 'trading-platform',
            url: 'https://example.com/platform',
            name: 'Platform Link',
            account_type_id: null,
            broker_id: $broker->id,
            zone_id: null,
        );

        $url = app(UrlService::class)->createAccountTypeUrl($dto, $broker->id, isAdmin: true);

        $this->assertInstanceOf(Url::class, $url);
        $this->assertSame('https://example.com/platform', $url->url);
        $this->assertSame('platform-link', $url->slug);
        $this->assertDatabaseHas('urls', [
            'id' => $url->id,
            'broker_id' => $broker->id,
            'url_type' => 'trading-platform',
        ]);
    }
}
