<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brokers\Database\Seeders\DropdownSeeder;
use Modules\Brokers\Models\DropdownCategory;
use Modules\Brokers\Models\DropdownOption;
use Symfony\Component\Intl\Currencies;
use Tests\TestCase;

class DropdownSeederTest extends TestCase
{
    use RefreshDatabase;

    //rulat cu: docker compose exec laravel php artisan test --compact Modules/Brokers/tests/Feature/DropdownSeederTest.php

    public function test_load_currency_dropdown_is_idempotent_and_updates_existing_options(): void
    {
        $category = DropdownCategory::create([
            'name' => 'Currency',
            'slug' => 'currency',
        ]);

        DropdownOption::create([
            'dropdown_category_id' => $category->id,
            'label' => 'Old USD',
            'value' => 'usd',
            'order' => 0,
        ]);

        $seeder = new DropdownSeeder();

        $seeder->loadCurrencyDropdown();
        $seeder->loadCurrencyDropdown();

        $expectedCount = count(array_unique(Currencies::getCurrencyCodes()));

        $this->assertSame(
            $expectedCount,
            DropdownOption::query()
                ->where('dropdown_category_id', $category->id)
                ->count()
        );

        $this->assertSame(
            1,
            DropdownOption::query()
                ->where('dropdown_category_id', $category->id)
                ->where('value', 'usd')
                ->count()
        );

        $this->assertSame(
            'USD',
            DropdownOption::query()
                ->where('dropdown_category_id', $category->id)
                ->where('value', 'usd')
                ->value('label')
        );
    }
}
