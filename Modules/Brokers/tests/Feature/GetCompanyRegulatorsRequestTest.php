<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerType;
use Modules\Brokers\Models\Company;
use Modules\Brokers\Services\CompanyService;
use Tests\TestCase;

class GetCompanyRegulatorsRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createBroker(): Broker
    {
        $brokerType = BrokerType::query()->create([
            'name' => 'Broker',
        ]);

        return Broker::query()->create([
            'broker_type_id' => $brokerType->id,
        ]);
    }

    public function test_it_returns_regulators_when_route_params_and_query_are_valid(): void
    {
        $broker = $this->createBroker();
        $company = Company::query()->create([
            'broker_id' => $broker->id,
        ]);

        $this->mock(CompanyService::class, function ($mock) use ($company): void {
            $mock->shouldReceive('getRegulators')
                ->once()
                ->with($company->id, 'en', null)
                ->andReturn(collect());
        });

        $response = $this->getJson(
            "/api/v1/companies/{$company->id}/broker/{$broker->id}/regulators"
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    public function test_it_defaults_language_code_to_en(): void
    {
        $broker = $this->createBroker();
        $company = Company::query()->create([
            'broker_id' => $broker->id,
        ]);

        $this->mock(CompanyService::class, function ($mock) use ($company): void {
            $mock->shouldReceive('getRegulators')
                ->once()
                ->with($company->id, 'en', null)
                ->andReturn(collect());
        });

        $this->getJson("/api/v1/companies/{$company->id}/broker/{$broker->id}/regulators")
            ->assertOk();
    }

    public function test_it_rejects_company_that_does_not_belong_to_broker(): void
    {
        $broker = $this->createBroker();
        $otherBroker = $this->createBroker();
        $company = Company::query()->create([
            'broker_id' => $broker->id,
        ]);

        $response = $this->getJson(
            "/api/v1/companies/{$company->id}/broker/{$otherBroker->id}/regulators?language_code=en"
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['company_id']);
    }

    public function test_it_rejects_nonexistent_broker(): void
    {
        $broker = $this->createBroker();
        $company = Company::query()->create([
            'broker_id' => $broker->id,
        ]);

        $response = $this->getJson(
            "/api/v1/companies/{$company->id}/broker/99999/regulators?language_code=en"
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['broker_id']);
    }

    public function test_it_rejects_nonexistent_company(): void
    {
        $broker = $this->createBroker();

        $response = $this->getJson(
            "/api/v1/companies/99999/broker/{$broker->id}/regulators?language_code=en"
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['company_id']);
    }
}
