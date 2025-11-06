<?php

namespace Modules\Brokers\Tests\Unit;

use Tests\TestCase;
use Mockery;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\Services\BrokerOptionService;
use Modules\Brokers\Repositories\BrokerOptionRepository;
use Modules\Brokers\Models\BrokerOption;

class BrokerOptionServiceTest extends TestCase
{
    /**
     * Test that service calls repository correctly
     * This shows how to mock the repository dependency
     */
    public function test_get_all_broker_options_calls_repository(): void
    {
        // Arrange: Mock the repository (dependency)
        $mockRepository = Mockery::mock(BrokerOptionRepository::class);
        
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator->shouldReceive('items')->andReturn([]);
        $mockPaginator->shouldReceive('currentPage')->andReturn(1);
        $mockPaginator->shouldReceive('lastPage')->andReturn(1);
        $mockPaginator->shouldReceive('perPage')->andReturn(15);
        $mockPaginator->shouldReceive('total')->andReturn(0);
        $mockPaginator->shouldReceive('firstItem')->andReturn(null);
        $mockPaginator->shouldReceive('lastItem')->andReturn(null);
        
        // Mock repository method
        $mockRepository->shouldReceive('getAllBrokerOptions')
            ->once()
            ->with([], 'id', 'asc', 15)
            ->andReturn($mockPaginator);
        
        // Act: Create REAL service with MOCKED repository
        $service = new BrokerOptionService($mockRepository);
        
        // Execute
        $result = $service->getAllBrokerOptions([], 'id', 'asc', 15);
        
        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test createBrokerOption with repository mock
     */
    public function test_create_broker_option_calls_repository(): void
    {
        // Arrange: Mock repository
        $mockRepository = Mockery::mock(BrokerOptionRepository::class);
        
        $mockBrokerOption = Mockery::mock(BrokerOption::class);
        
        $data = [
            'name' => 'Test Option',
            'slug' => 'test_option',
            'category_name' => 1, // Will be converted to option_category_id
        ];
        
        $expectedData = [
            'name' => 'Test Option',
            'slug' => 'test_option',
            'option_category_id' => 1, // Converted
            'default_language' => 'en', // Added by service
        ];
        
        // Mock repository create method
        $mockRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedData) {
                // Verify the data transformation happened
                return isset($arg['option_category_id']) 
                    && $arg['option_category_id'] === 1
                    && isset($arg['default_language'])
                    && $arg['default_language'] === 'en';
            }))
            ->andReturn($mockBrokerOption);
        
        // Act: Create service with mocked repository
        $service = new BrokerOptionService($mockRepository);
        
        // Execute
        $result = $service->createBrokerOption($data);
        
        // Assert
        $this->assertInstanceOf(BrokerOption::class, $result);
    }

    /**
     * Test updateBrokerOption with repository mock
     */
    public function test_update_broker_option_calls_repository(): void
    {
        // Arrange
        $mockRepository = Mockery::mock(BrokerOptionRepository::class);
        $mockBrokerOption = Mockery::mock(BrokerOption::class);
        
        $data = [
            'name' => 'Updated Option',
            'category_name' => 2,
        ];
        
        $mockRepository->shouldReceive('update')
            ->once()
            ->with(
                Mockery::on(function ($arg) {
                    return isset($arg['option_category_id']) && $arg['option_category_id'] === 2;
                }),
                1
            )
            ->andReturn($mockBrokerOption);
        
        // Act
        $service = new BrokerOptionService($mockRepository);
        $result = $service->updateBrokerOption($data, 1);
        
        // Assert
        $this->assertInstanceOf(BrokerOption::class, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

