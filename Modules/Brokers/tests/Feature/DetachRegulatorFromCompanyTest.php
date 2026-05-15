<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Modules\Brokers\Models\Company;
use Modules\Brokers\Models\Regulator;
use Modules\Brokers\Models\Zone;
use Tests\TestCase;

class DetachRegulatorFromCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_detaches_regulator_from_company(): void
    {
        $brokerType = BrokerType::query()->create(['name' => 'Broker']);
        $broker = Broker::query()->create(['broker_type_id' => $brokerType->id]);
        $company = Company::query()->create(['broker_id' => $broker->id]);
        $regulator = Regulator::query()->create([
            'name' => 'FCA',
            'acronym' => 'FCA',
            'is_invariant' => true,
        ]);
        $company->regulators()->attach($regulator->id);

        $response = $this->deleteJson("/api/v1/regulators/{$regulator->id}/company/{$company->id}/broker/{$broker->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Regulator detached from company successfully',
            ]);

        $this->assertDatabaseMissing('company_regulator', [
            'company_id' => $company->id,
            'regulator_id' => $regulator->id,
        ]);
    }

    public function test_it_detaches_only_matching_pivot_zone_id(): void
    {
        $brokerType = BrokerType::query()->create(['name' => 'Broker']);
        $broker = Broker::query()->create(['broker_type_id' => $brokerType->id]);
        $company = Company::query()->create(['broker_id' => $broker->id]);
        $regulator = Regulator::query()->create([
            'name' => 'FCA',
            'acronym' => 'FCA',
            'is_invariant' => true,
        ]);
        $zoneOne = Zone::query()->create(['name' => 'EU', 'zone_code' => 'eu']);
        $zoneTwo = Zone::query()->create(['name' => 'US', 'zone_code' => 'us']);
        $company->regulators()->attach($regulator->id, ['zone_id' => $zoneOne->id]);
        $company->regulators()->attach($regulator->id, ['zone_id' => $zoneTwo->id]);

        $this->deleteJson("/api/v1/regulators/{$regulator->id}/company/{$company->id}/broker/{$broker->id}", [
            'zone_id' => $zoneOne->id,
        ])->assertOk();

        $this->assertDatabaseMissing('company_regulator', [
            'company_id' => $company->id,
            'regulator_id' => $regulator->id,
            'zone_id' => $zoneOne->id,
        ]);
        $this->assertDatabaseHas('company_regulator', [
            'company_id' => $company->id,
            'regulator_id' => $regulator->id,
            'zone_id' => $zoneTwo->id,
        ]);
    }

    public function test_it_rejects_company_that_does_not_belong_to_broker(): void
    {
        $brokerType = BrokerType::query()->create(['name' => 'Broker']);
        $broker = Broker::query()->create(['broker_type_id' => $brokerType->id]);
        $otherBroker = Broker::query()->create(['broker_type_id' => $brokerType->id]);
        $company = Company::query()->create(['broker_id' => $broker->id]);
        $regulator = Regulator::query()->create([
            'name' => 'FCA',
            'acronym' => 'FCA',
            'is_invariant' => true,
        ]);

        $response = $this->deleteJson("/api/v1/regulators/{$regulator->id}/company/{$company->id}/broker/{$otherBroker->id}");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['company_id']);
    }
}
