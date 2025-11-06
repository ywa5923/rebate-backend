<?php

namespace Modules\Brokers\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brokers\Repositories\BrokerOptionRepository;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionCategory;
use Modules\Brokers\Models\DropdownCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BrokerOptionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected BrokerOptionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BrokerOptionRepository(new BrokerOption());
    }

    /**
     * Test getAllBrokerOptions returns paginated results
     */
    public function test_get_all_broker_options_returns_paginated_results(): void
    {
        // Arrange: Create test data
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test Description',
            'position' => 1,
        ]);
        
        for ($i = 1; $i <= 20; $i++) {
            BrokerOption::create([
                'name' => 'Test Option ' . $i,
                'slug' => 'test_option_' . $i,
                'applicable_for' => 'broker',
                'data_type' => 'string',
                'form_type' => 'text',
                'for_crypto' => 0,
                'for_brokers' => 1,
                'for_props' => 0,
                'required' => 1,
                'default_language' => 'en',
                'option_category_id' => $category->id,
            ]);
        }

        // ===== ALTERNATIVE: Using Factories (requires HasFactory trait in models) =====
        // To use factories, first add 'use HasFactory;' trait to BrokerOption and OptionCategory models
        // 
        // IMPORTANT: For THIS test, you can use factory defaults because:
        // - Test only checks pagination (count, total, perPage) - doesn't care about specific values
        // - No assertions check for specific names or other field values
        // 
        // Option 1: Use factory defaults (simpler - works for this test)
        // $category = OptionCategory::factory()->create();
        // BrokerOption::factory()->count(20)->create(['option_category_id' => $category->id]);
        // 
        // Option 2: Explicit values (only needed if test assertions depend on specific values)
        // $category = OptionCategory::factory()->create(['name' => 'Test Category']);
        // for ($i = 1; $i <= 20; $i++) {
        //     BrokerOption::factory()->create([
        //         'name' => 'Test Option ' . $i,
        //         'slug' => 'test_option_' . $i,
        //         'option_category_id' => $category->id,
        //         'for_brokers' => 1,
        //         'for_crypto' => 0,
        //         'for_props' => 0,
        //         'required' => 1,
        //         'default_language' => 'en',
        //     ]);
        // }
        // 
        // Use explicit values ONLY when:
        // - Test filters by specific name/value (see test_get_all_broker_options_filters_by_name)
        // - Test asserts specific field values
        // - Test needs predictable data for relationships
        // ==============================================================================

        // Act
        $result = $this->repository->getAllBrokerOptions([], 'id', 'asc', 15);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(20, $result->total());
        $this->assertEquals(15, $result->perPage());
        $this->assertEquals(1, $result->currentPage());
        $this->assertCount(15, $result->items());
    }

    /**
     * Test getAllBrokerOptions with name filter
     */
    public function test_get_all_broker_options_filters_by_name(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        
        BrokerOption::create([
            'name' => 'Trading Account',
            'slug' => 'trading_account',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);
        
        BrokerOption::create([
            'name' => 'Deposit Method',
            'slug' => 'deposit_method',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);

        // ===== ALTERNATIVE: Using Factories =====
        // NOTE: For THIS test, you MUST use explicit values because:
        // - Test filters by name: ['name' => 'Trading']
        // - Test asserts specific name: assertEquals('Trading Account', ...)
        // - Factory defaults generate random names, so you can't reliably test filtering
        // 
        // $category = OptionCategory::factory()->create();
        // BrokerOption::factory()->create([
        //     'name' => 'Trading Account',  // REQUIRED: Test filters by this name
        //     'slug' => 'trading_account',
        //     'option_category_id' => $category->id,
        //     'for_brokers' => 1,
        //     'for_crypto' => 0,
        //     'for_props' => 0,
        //     'required' => 1,
        //     'default_language' => 'en',
        // ]);
        // BrokerOption::factory()->create([
        //     'name' => 'Deposit Method',  // REQUIRED: Test needs this to verify filter works
        //     'slug' => 'deposit_method',
        //     'option_category_id' => $category->id,
        //     'for_brokers' => 1,
        //     'for_crypto' => 0,
        //     'for_props' => 0,
        //     'required' => 1,
        //     'default_language' => 'en',
        // ]);
        // =========================================

        // Act
        $result = $this->repository->getAllBrokerOptions(
            ['name' => 'Trading'],
            'id',
            'asc',
            15
        );

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals('Trading Account', $result->items()[0]->name);
    }

    /**
     * Test getAllBrokerOptions with category_name filter
     */
    public function test_get_all_broker_options_filters_by_category_name(): void
    {
        // Arrange
        $category1 = OptionCategory::create([
            'name' => 'Account Settings',
            'description' => 'Test',
            'position' => 1,
        ]);
        $category2 = OptionCategory::create([
            'name' => 'Payment Methods',
            'description' => 'Test',
            'position' => 2,
        ]);
        
        BrokerOption::create([
            'name' => 'Option 1',
            'slug' => 'option_1',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category1->id,
        ]);
        
        BrokerOption::create([
            'name' => 'Option 2',
            'slug' => 'option_2',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category2->id,
        ]);

        // Act
        $result = $this->repository->getAllBrokerOptions(
            ['category_name' => 'Account'],
            'id',
            'asc',
            15
        );

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals('Option 1', $result->items()[0]->name);
        $this->assertEquals('Account Settings', $result->items()[0]->category->name);
    }

    /**
     * Test getAllBrokerOptions with for_brokers filter
     */
    public function test_get_all_broker_options_filters_by_for_brokers(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        
        BrokerOption::create([
            'name' => 'Option 1',
            'slug' => 'option_1',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);
        
        BrokerOption::create([
            'name' => 'Option 2',
            'slug' => 'option_2',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 0,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);

        // Act
        $result = $this->repository->getAllBrokerOptions(
            ['for_brokers' => 1],
            'id',
            'asc',
            15
        );

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals(1, $result->items()[0]->for_brokers);
    }

    /**
     * Test getAllBrokerOptions with ordering by category_name
     */
    public function test_get_all_broker_options_orders_by_category_name(): void
    {
        // Arrange
        $categoryA = OptionCategory::create([
            'name' => 'Zebra Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        $categoryB = OptionCategory::create([
            'name' => 'Alpha Category',
            'description' => 'Test',
            'position' => 2,
        ]);
        
        BrokerOption::create([
            'name' => 'Option 1',
            'slug' => 'option_1',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $categoryA->id,
        ]);
        
        BrokerOption::create([
            'name' => 'Option 2',
            'slug' => 'option_2',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $categoryB->id,
        ]);

        // Act: Order by category name ascending
        $result = $this->repository->getAllBrokerOptions(
            [],
            'category_name',
            'asc',
            15
        );

        // Assert: Should be ordered by category name (Alpha before Zebra)
        $items = $result->items();
        $this->assertEquals('Option 2', $items[0]->name); // Alpha Category
        $this->assertEquals('Option 1', $items[1]->name); // Zebra Category
    }

    /**
     * Test getAllBrokerOptions loads relationships
     */
    public function test_get_all_broker_options_loads_relationships(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        $dropdownCategory = DropdownCategory::create([
            'name' => 'Test Dropdown',
            'slug' => 'test_dropdown',
            'description' => 'Test',
        ]);
        
        BrokerOption::create([
            'name' => 'Test Option',
            'slug' => 'test_option',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
            'dropdown_category_id' => $dropdownCategory->id,
        ]);

        // ===== ALTERNATIVE: Using Factories =====
        // $category = OptionCategory::factory()->create(['name' => 'Test Category']);
        // $dropdownCategory = DropdownCategory::factory()->create(['name' => 'Test Dropdown']);
        // BrokerOption::factory()->create([
        //     'name' => 'Test Option',
        //     'slug' => 'test_option',
        //     'option_category_id' => $category->id,
        //     'dropdown_category_id' => $dropdownCategory->id,
        //     'for_brokers' => 1,
        //     'for_crypto' => 0,
        //     'for_props' => 0,
        //     'required' => 1,
        //     'default_language' => 'en',
        // ]);
        // =========================================

        // Act
        $result = $this->repository->getAllBrokerOptions([], 'id', 'asc', 15);

        // Assert: Relationships should be loaded
        $option = $result->items()[0];
        $this->assertTrue($option->relationLoaded('category'));
        $this->assertTrue($option->relationLoaded('dropdownCategory'));
        $this->assertEquals('Test Category', $option->category->name);
        $this->assertEquals('Test Dropdown', $option->dropdownCategory->name);
    }

    /**
     * Test create method inserts into database
     */
    public function test_create_inserts_into_database(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        
        $data = [
            'name' => 'New Option',
            'slug' => 'new_option',
            'applicable_for' => 'broker',
            'data_type' => 'string',
            'form_type' => 'text',
            'meta_data' => ['key' => 'value'],
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ];

        // ===== ALTERNATIVE: Using Factories =====
        // $category = OptionCategory::factory()->create();
        // $data = BrokerOption::factory()->make([
        //     'name' => 'New Option',
        //     'slug' => 'new_option',
        //     'applicable_for' => 'broker',
        //     'meta_data' => ['key' => 'value'],
        //     'for_crypto' => 0,
        //     'for_brokers' => 1,
        //     'for_props' => 0,
        //     'required' => 1,
        //     'default_language' => 'en',
        //     'option_category_id' => $category->id,
        // ])->toArray();
        // Note: factory()->make() creates model instance without saving
        // factory()->create() would save directly to database
        // =========================================

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(BrokerOption::class, $result);
        $this->assertDatabaseHas('broker_options', [
            'name' => 'New Option',
            'slug' => 'new_option',
            'option_category_id' => $category->id,
        ]);
        $this->assertEquals('New Option', $result->name);
    }

    /**
     * Test create method handles JSON meta_data correctly
     */
    public function test_create_handles_json_meta_data(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        
        $metaData = ['additional' => 'data', 'nested' => ['key' => 'value']];
        
        $data = [
            'name' => 'Test Option',
            'slug' => 'test_option',
            'data_type' => 'string',
            'form_type' => 'text',
            'meta_data' => $metaData,
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ];

        // Act
        $result = $this->repository->create($data);

        // Assert: meta_data should be stored as JSON and retrieved as array
        $this->assertIsArray($result->meta_data);
        $this->assertEquals('data', $result->meta_data['additional']);
        $this->assertEquals('value', $result->meta_data['nested']['key']);
        
        // Verify in database (should be JSON string)
        $dbRecord = DB::table('broker_options')->where('id', $result->id)->first();
        $this->assertJson($dbRecord->meta_data);
    }

    /**
     * Test update method modifies database record
     */
    public function test_update_modifies_database_record(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        $option = BrokerOption::create([
            'name' => 'Original Name',
            'slug' => 'original_name',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'slug' => 'updated_slug',
        ];

        // Act
        $result = $this->repository->update($updateData, $option->id);

        // Assert
        $this->assertInstanceOf(BrokerOption::class, $result);
        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals('updated_slug', $result->slug);
        
        $this->assertDatabaseHas('broker_options', [
            'id' => $option->id,
            'name' => 'Updated Name',
            'slug' => 'updated_slug',
        ]);
    }

    /**
     * Test update loads relationships
     */
    public function test_update_loads_relationships(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        $option = BrokerOption::create([
            'name' => 'Test Option',
            'slug' => 'test_option',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);

        // Act
        $result = $this->repository->update(['name' => 'Updated'], $option->id);

        // Assert: Relationships should be loaded
        $this->assertTrue($result->relationLoaded('category'));
        $this->assertTrue($result->relationLoaded('dropdownCategory'));
        $this->assertEquals('Test Category', $result->category->name);
    }

    /**
     * Test delete method removes from database
     */
    public function test_delete_removes_from_database(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        $option = BrokerOption::create([
            'name' => 'Test Option',
            'slug' => 'test_option',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);

        // Act
        $result = $this->repository->delete($option->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('broker_options', [
            'id' => $option->id,
        ]);
    }

    /**
     * Test delete returns false for non-existent record
     */
    public function test_delete_returns_false_for_non_existent_record(): void
    {
        // Act
        $result = $this->repository->delete(99999);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test getAllBrokerOptions with multiple filters
     */
    public function test_get_all_broker_options_with_multiple_filters(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Account Settings',
            'description' => 'Test',
            'position' => 1,
        ]);
        
        BrokerOption::create([
            'name' => 'Trading Account',
            'slug' => 'trading_account',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);
        
        BrokerOption::create([
            'name' => 'Deposit Method',
            'slug' => 'deposit_method',
            'data_type' => 'number',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 0,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);

        // Act: Filter by name, for_brokers, and data_type
        $result = $this->repository->getAllBrokerOptions(
            [
                'name' => 'Trading',
                'for_brokers' => 1,
                'data_type' => 'string',
            ],
            'id',
            'asc',
            15
        );

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals('Trading Account', $result->items()[0]->name);
    }

    /**
     * Test getAllBrokerOptions with ordering
     */
    public function test_get_all_broker_options_with_ordering(): void
    {
        // Arrange
        $category = OptionCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'position' => 1,
        ]);
        
        BrokerOption::create([
            'name' => 'Zebra Option',
            'slug' => 'zebra_option',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);
        
        BrokerOption::create([
            'name' => 'Alpha Option',
            'slug' => 'alpha_option',
            'data_type' => 'string',
            'form_type' => 'text',
            'for_crypto' => 0,
            'for_brokers' => 1,
            'for_props' => 0,
            'required' => 1,
            'default_language' => 'en',
            'option_category_id' => $category->id,
        ]);

        // Act: Order by name descending
        $result = $this->repository->getAllBrokerOptions(
            [],
            'name',
            'desc',
            15
        );

        // Assert: Should be ordered descending (Zebra before Alpha)
        $items = $result->items();
        $this->assertEquals('Zebra Option', $items[0]->name);
        $this->assertEquals('Alpha Option', $items[1]->name);
    }
}

