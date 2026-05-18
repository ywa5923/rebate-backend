<?php

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\Database\Seeders\RegulatorsSeeder;
use PHPUnit\Framework\TestCase;

class RegulatorsSeederTest extends TestCase
{
    public function test_normalize_rating_extracts_numeric_value_from_stars(): void
    {
        $this->assertSame('5.00', RegulatorsSeeder::normalizeRating('5 stars'));
        $this->assertSame('3.00', RegulatorsSeeder::normalizeRating('3 stars'));
        $this->assertSame('1.00', RegulatorsSeeder::normalizeRating('1 star'));
        $this->assertNull(RegulatorsSeeder::normalizeRating('N/A'));
    }

    public function test_normalize_year_parses_four_digit_year(): void
    {
        $this->assertSame(2013, RegulatorsSeeder::normalizeYear('2013'));
        $this->assertNull(RegulatorsSeeder::normalizeYear('N/A'));
    }
}
