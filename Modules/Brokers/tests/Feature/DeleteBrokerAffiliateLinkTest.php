<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Models\AffliliateLink;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Services\UrlService;
use Modules\Translations\Models\Translation;
use Tests\TestCase;

class DeleteBrokerAffiliateLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_affiliate_link_and_clears_related_pivot_and_translations(): void
    {
        $brokerType = BrokerType::query()->create([
            'name' => 'Broker',
        ]);

        $broker = Broker::query()->create([
            'broker_type_id' => $brokerType->id,
        ]);

        $platformUrl = Url::query()->create([
            'broker_id' => $broker->id,
            'url_type' => 'webplatform',
            'url' => 'https://example.com/platform',
            'name' => 'Platform Link',
            'slug' => 'platform-link',
        ]);

        $affiliateLink = AffliliateLink::query()->create([
            'broker_id' => $broker->id,
            'affiliate_type' => 'ib-affiliate-link',
            'name' => 'Affiliate Link',
            'url' => 'https://example.com/affiliate',
            'currency' => 'USD',
            'is_master_link' => false,
        ]);

        $affiliateLink->platformUrls()->attach($platformUrl->id, [
            'is_public' => true,
            'is_updated_entry' => false,
        ]);

        Translation::query()->insert([
            'translationable_type' => AffliliateLink::class,
            'translationable_id' => $affiliateLink->id,
            'language_code' => 'en',
            'property' => 'name',
            'value' => 'Affiliate Link',
            'translation_type' => 'property',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(UrlService::class);

        $this->assertDatabaseHas('affliliate_link_url', [
            'affliliate_link_id' => $affiliateLink->id,
            'url_id' => $platformUrl->id,
        ]);
        $this->assertDatabaseHas('translations', [
            'translationable_type' => AffliliateLink::class,
            'translationable_id' => $affiliateLink->id,
        ]);

        $this->assertTrue($service->deleteBrokerAffiliateLink($broker->id, $affiliateLink->id));

        $this->assertDatabaseMissing('affliliate_links', [
            'id' => $affiliateLink->id,
        ]);
        $this->assertModelExists($platformUrl->fresh());
        $this->assertDatabaseMissing('affliliate_link_url', [
            'affliliate_link_id' => $affiliateLink->id,
            'url_id' => $platformUrl->id,
        ]);
        $this->assertDatabaseMissing('translations', [
            'translationable_type' => AffliliateLink::class,
            'translationable_id' => $affiliateLink->id,
        ]);
    }
}
