<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Database\Seeders\CountryAndZoneSeeder;
use Modules\Brokers\Models\Country;
use Modules\Brokers\Models\Zone;
use Tests\TestCase;

class CountryAndZoneSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_zones_then_countries_with_zone_id(): void
    {
        $this->seed(CountryAndZoneSeeder::class);

        $this->assertSame(7, Zone::query()->count());
        $this->assertSame(120, Country::query()->count());

        $northAmerica = Zone::query()->where('zone_code', 'NA')->first();
        $unitedStates = Country::query()->where('country_code', 'US')->first();

        $this->assertNotNull($northAmerica);
        $this->assertNotNull($unitedStates);
        $this->assertSame($northAmerica->id, $unitedStates->zone_id);
        $this->assertSame('United States', $unitedStates->name);

        $namibia = Country::query()->where('country_code', 'NA')->first();
        $subSaharanAfrica = Zone::query()->where('zone_code', 'SSA')->first();

        $this->assertNotNull($namibia);
        $this->assertNotNull($subSaharanAfrica);
        $this->assertSame($subSaharanAfrica->id, $namibia->zone_id);
        $this->assertSame('Namibia', $namibia->name);
    }
}
