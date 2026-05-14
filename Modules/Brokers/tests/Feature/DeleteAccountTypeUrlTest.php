<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Services\UrlService;
use Tests\TestCase;

class DeleteAccountTypeUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_account_type_url_returns_snapshot_and_removes_row(): void
    {
        $brokerType = BrokerType::query()->create([
            'name' => 'Broker',
        ]);

        $broker = Broker::query()->create([
            'broker_type_id' => $brokerType->id,
        ]);

        $url = Url::query()->create([
            'broker_id' => $broker->id,
            'url_type' => 'webplatform',
            'url' => 'https://example.com/platform',
            'name' => 'Platform Link',
            'slug' => 'platform-link',
        ]);

        $service = app(UrlService::class);

        $deleted = $service->deleteAccountTypeUrl($broker->id, $url->id);

        $this->assertSame($url->id, $deleted['id']);
        $this->assertSame($broker->id, $deleted['broker_id']);
        $this->assertSame('https://example.com/platform', $deleted['url']);
        $this->assertDatabaseMissing('urls', [
            'id' => $url->id,
        ]);
    }
}
