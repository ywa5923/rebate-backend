<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Database\Seeders\RegulatorsSeeder;
use Modules\Brokers\Models\Regulator;
use Tests\TestCase;

class RegulatorsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_regulators_from_default_csv(): void
    {
        $this->seed(RegulatorsSeeder::class);

        $fca = Regulator::query()->where('acronym', 'FCA')->first();

        $this->assertNotNull($fca);
        $this->assertSame('Financial Conduct Authority', $fca->name);
        $this->assertSame('United Kingdom', $fca->country);
        $this->assertSame('Europe', $fca->zone);
        $this->assertSame('Tier-1', $fca->tier_classification);
        $this->assertSame('https://www.fca.org.uk', $fca->website);
        $this->assertSame(2013, $fca->year_established);
        $this->assertTrue($fca->is_invariant);
        $this->assertGreaterThan(50, Regulator::query()->count());
    }
}
