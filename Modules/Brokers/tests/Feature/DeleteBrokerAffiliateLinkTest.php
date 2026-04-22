<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Models\UrlAssociations;
use Modules\Brokers\Services\UrlService;
use Tests\TestCase;

class DeleteBrokerAffiliateLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_affiliate_link_and_clears_all_url_associations(): void
    {
        $brokerType = BrokerType::query()->create([
            'name' => 'Broker',
        ]);

        $broker = Broker::query()->create([
            'broker_type_id' => $brokerType->id,
        ]);

        $affiliateUrl = Url::query()->create([
            'broker_id' => $broker->id,
            'url_type' => 'ib-affiliate-link',
            'url' => 'https://example.com/affiliate',
            'name' => 'Affiliate Link',
            'slug' => 'affiliate-link',
        ]);

        $platformUrl = Url::query()->create([
            'broker_id' => $broker->id,
            'url_type' => 'webplatform',
            'url' => 'https://example.com/platform',
            'name' => 'Platform Link',
            'slug' => 'platform-link',
        ]);

        $referrerUrl = Url::query()->create([
            'broker_id' => $broker->id,
            'url_type' => 'sub-ib-affiliate-link',
            'url' => 'https://example.com/referrer',
            'name' => 'Referrer Link',
            'slug' => 'referrer-link',
        ]);

        $affiliateUrl->associatedUrls()->attach($platformUrl->id, [
            'is_public' => true,
            'is_updated_entry' => false,
        ]);

        $referrerUrl->associatedUrls()->attach($affiliateUrl->id, [
            'is_public' => false,
            'is_updated_entry' => true,
        ]);

        $service = app(UrlService::class);

        $this->assertSame(2, UrlAssociations::query()->count());

        $this->assertTrue($service->deleteBrokerAffiliateLink($broker->id, $affiliateUrl->id));

        $this->assertDatabaseMissing('urls', [
            'id' => $affiliateUrl->id,
        ]);
        $this->assertModelExists($platformUrl->fresh());
        $this->assertModelExists($referrerUrl->fresh());
        $this->assertSame(0, UrlAssociations::query()->count());
    }
}
