<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Modules\Brokers\Models\Company;
use Modules\Brokers\Models\Regulator;
use Tests\TestCase;

class AttachRegulatorToCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_attaches_regulator_to_company(): void
    {
        $brokerType = BrokerType::query()->create(['name' => 'Broker']);
        $broker = Broker::query()->create(['broker_type_id' => $brokerType->id]);
        $company = Company::query()->create(['broker_id' => $broker->id]);
        $regulator = Regulator::query()->create([
            'name' => 'FCA',
            'acronym' => 'FCA',
            'is_invariant' => true,
        ]);

        $response = $this->postJson("/api/v1/regulators/{$regulator->id}/company/{$company->id}/broker/{$broker->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $regulator->id,
                    'name' => 'FCA',
                    'acronym' => 'FCA',
                ],
            ]);

        $this->assertDatabaseHas('company_regulator', [
            'company_id' => $company->id,
            'regulator_id' => $regulator->id,
            'zone_id' => null,
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

        $response = $this->postJson("/api/v1/regulators/{$regulator->id}/company/{$company->id}/broker/{$otherBroker->id}");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['company_id']);
    }

    public function test_it_rejects_invalid_regulator_id(): void
    {
        $brokerType = BrokerType::query()->create(['name' => 'Broker']);
        $broker = Broker::query()->create(['broker_type_id' => $brokerType->id]);
        $company = Company::query()->create(['broker_id' => $broker->id]);

        $response = $this->postJson("/api/v1/regulators/99999/company/{$company->id}/broker/{$broker->id}");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['regulator_id']);
    }
}
