<?php

namespace Modules\Brokers\Tests\Unit;

use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\Http\Controllers\BrokerOptionController;
use Modules\Brokers\Services\BrokerOptionService;
use Modules\Brokers\Table\TableConfigInterface;
use Modules\Brokers\Http\Requests\BrokerOptionListRequest;
use Modules\Brokers\Transformers\BrokerOptionCollection;

class BrokerOptionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that getBrokerOptionsList uses the table config interface
     * and returns the correct structure with mocked config.
     */
    public function test_get_broker_options_list_uses_table_config_interface(): void
    {
        // Arrange: Create mocks
        $mockTableConfig = Mockery::mock(TableConfigInterface::class);
        $mockBrokerOptionService = Mockery::mock(BrokerOptionService::class);
        
        // Mock the table config methods
        $mockTableConfig->shouldReceive('columns')
            ->once()
            ->andReturn([
                'id' => ['label' => 'ID', 'visible' => true, 'sortable' => true],
                'name' => ['label' => 'Name', 'visible' => true, 'sortable' => true],
            ]);
        
        $mockTableConfig->shouldReceive('filters')
            ->once()
            ->andReturn([
                'name' => ['label' => 'Name', 'type' => 'text'],
                'applicable_for' => ['label' => 'Applicable For', 'type' => 'text'],
            ]);
        
        // Mock paginated result
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator->shouldReceive('items')->andReturn([]);
        $mockPaginator->shouldReceive('currentPage')->andReturn(1);
        $mockPaginator->shouldReceive('lastPage')->andReturn(1);
        $mockPaginator->shouldReceive('perPage')->andReturn(15);
        $mockPaginator->shouldReceive('total')->andReturn(0);
        $mockPaginator->shouldReceive('firstItem')->andReturn(null);
        $mockPaginator->shouldReceive('lastItem')->andReturn(null);
        
        $mockBrokerOptionService->shouldReceive('getAllBrokerOptions')
            ->once()
            ->with([], 'id', 'asc', 15)
            ->andReturn($mockPaginator);
        
        // Mock the request
        $mockRequest = Mockery::mock(BrokerOptionListRequest::class);
        $mockRequest->shouldReceive('getFilters')->andReturn([]);
        $mockRequest->shouldReceive('getOrderBy')->andReturn('id');
        $mockRequest->shouldReceive('getOrderDirection')->andReturn('asc');
        $mockRequest->shouldReceive('getPerPage')->andReturn(15);
        
        // Bind mocks to container
        $this->app->instance(BrokerOptionService::class, $mockBrokerOptionService);
        $this->app->instance(TableConfigInterface::class, $mockTableConfig);
        
        // Act: Create controller instance (will use mocked dependencies)
        $controller = new BrokerOptionController(
            $mockBrokerOptionService,
            $mockTableConfig
        );
        
        // Execute the method
        $response = $controller->getBrokerOptionsList($mockRequest);
        
        // Assert: Check response structure
        $this->assertInstanceOf(Response::class, $response);
        
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('table_columns_config', $responseData);
        $this->assertArrayHasKey('filters_config', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        
        // Verify the mocked config data is in the response
        $this->assertEquals('ID', $responseData['table_columns_config']['id']['label']);
        $this->assertEquals('Name', $responseData['table_columns_config']['name']['label']);
        $this->assertEquals('Name', $responseData['filters_config']['name']['label']);
    }

    /**
     * Test that table config interface is called correctly
     * This is a more focused unit test.
     */
    public function test_table_config_interface_methods_are_called(): void
    {
        // Arrange
        $mockTableConfig = Mockery::mock(TableConfigInterface::class);
        $mockBrokerOptionService = Mockery::mock(BrokerOptionService::class);
        
        // Set expectations - verify methods are called
        $mockTableConfig->shouldReceive('columns')
            ->once()
            ->andReturn(['id' => ['label' => 'ID']]);
        
        $mockTableConfig->shouldReceive('filters')
            ->once()
            ->andReturn(['name' => ['label' => 'Name']]);
        
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator->shouldReceive('items')->andReturn([]);
        $mockPaginator->shouldReceive('currentPage')->andReturn(1);
        $mockPaginator->shouldReceive('lastPage')->andReturn(1);
        $mockPaginator->shouldReceive('perPage')->andReturn(15);
        $mockPaginator->shouldReceive('total')->andReturn(0);
        $mockPaginator->shouldReceive('firstItem')->andReturn(null);
        $mockPaginator->shouldReceive('lastItem')->andReturn(null);
        
        $mockBrokerOptionService->shouldReceive('getAllBrokerOptions')
            ->andReturn($mockPaginator);
        
        $mockRequest = Mockery::mock(BrokerOptionListRequest::class);
        $mockRequest->shouldReceive('getFilters')->andReturn([]);
        $mockRequest->shouldReceive('getOrderBy')->andReturn('id');
        $mockRequest->shouldReceive('getOrderDirection')->andReturn('asc');
        $mockRequest->shouldReceive('getPerPage')->andReturn(15);
        
        // Act
        $controller = new BrokerOptionController(
            $mockBrokerOptionService,
            $mockTableConfig
        );
        
        $controller->getBrokerOptionsList($mockRequest);
        
        // Assert: Mockery will automatically verify expectations
        // If columns() or filters() weren't called, this test would fail
        Mockery::close();
    }

    /**
     * Test with different table config values
     * Demonstrates flexibility of mocking the interface.
     */
    public function test_different_table_config_values(): void
    {
        // Arrange: Mock with different values
        $mockTableConfig = Mockery::mock(TableConfigInterface::class);
        $mockBrokerOptionService = Mockery::mock(BrokerOptionService::class);
        
        // Return different config structure
        $mockTableConfig->shouldReceive('columns')
            ->andReturn([
                'custom_field' => ['label' => 'Custom Field', 'visible' => false],
            ]);
        
        $mockTableConfig->shouldReceive('filters')
            ->andReturn([
                'custom_filter' => ['label' => 'Custom Filter', 'type' => 'select'],
            ]);
        
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator->shouldReceive('items')->andReturn([]);
        $mockPaginator->shouldReceive('currentPage')->andReturn(1);
        $mockPaginator->shouldReceive('lastPage')->andReturn(1);
        $mockPaginator->shouldReceive('perPage')->andReturn(15);
        $mockPaginator->shouldReceive('total')->andReturn(0);
        $mockPaginator->shouldReceive('firstItem')->andReturn(null);
        $mockPaginator->shouldReceive('lastItem')->andReturn(null);
        
        $mockBrokerOptionService->shouldReceive('getAllBrokerOptions')
            ->andReturn($mockPaginator);
        
        $mockRequest = Mockery::mock(BrokerOptionListRequest::class);
        $mockRequest->shouldReceive('getFilters')->andReturn([]);
        $mockRequest->shouldReceive('getOrderBy')->andReturn('id');
        $mockRequest->shouldReceive('getOrderDirection')->andReturn('asc');
        $mockRequest->shouldReceive('getPerPage')->andReturn(15);
        
        // Act
        $controller = new BrokerOptionController(
            $mockBrokerOptionService,
            $mockTableConfig
        );
        
        $response = $controller->getBrokerOptionsList($mockRequest);
        $responseData = json_decode($response->getContent(), true);
        
        // Assert: Verify custom config is used
        $this->assertArrayHasKey('custom_field', $responseData['table_columns_config']);
        $this->assertArrayHasKey('custom_filter', $responseData['filters_config']);
        $this->assertEquals('Custom Field', $responseData['table_columns_config']['custom_field']['label']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

