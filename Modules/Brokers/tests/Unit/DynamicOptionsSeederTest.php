<?php

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\Database\Seeders\DynamicOptionsSeeder;
use PHPUnit\Framework\TestCase;

class DynamicOptionsSeederTest extends TestCase
{
    public function test_resolve_dropdown_category_id_treats_csv_null_literal_as_null(): void
    {
        $this->assertNull(DynamicOptionsSeeder::resolveDropdownCategoryId('NULL', 'logo'));
        $this->assertNull(DynamicOptionsSeeder::resolveDropdownCategoryId(null, 'logo'));
        $this->assertNull(DynamicOptionsSeeder::resolveDropdownCategoryId('', 'logo'));
    }

    public function test_resolve_dropdown_category_id_casts_numeric_strings(): void
    {
        $this->assertSame(3, DynamicOptionsSeeder::resolveDropdownCategoryId('3', 'logo'));
    }

    public function test_normalize_meta_data_encodes_arrays_for_db_insert(): void
    {
        $this->assertNull(DynamicOptionsSeeder::normalizeMetaData('NULL'));
        $this->assertSame(
            '{"error":"missing"}',
            DynamicOptionsSeeder::normalizeMetaData(['error' => 'missing'])
        );
    }
}
