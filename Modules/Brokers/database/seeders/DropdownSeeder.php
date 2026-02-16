<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\DropdownCategory;
use Modules\Brokers\Models\DropdownOption;
use Symfony\Component\Intl\Currencies;

class DropdownSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        $this->loadCurrencyDropdown();
        $this->loadUnitDropdown();
    }

    public function loadCurrencyDropdown(){
        // Make category idempotent
        $category = DropdownCategory::firstOrCreate(
            ['slug' => 'currency'],
            ['name' => 'Currency']
        );
        $id = $category->id;

        $codes = Currencies::getCurrencyCodes();

        $now = now();
        $rows = collect($codes)->unique()->map(static function (string $code) use ($id, $now) {
            $code = strtoupper($code);
            return [
                'label' => $code,
                'value' => strtolower($code),
                'dropdown_category_id' => $id,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        })->values()->all();

        // Upsert ensures idempotency and avoids duplicates per category+value
        DropdownOption::upsert(
            $rows,
            ['dropdown_category_id', 'value'],
            ['label', 'updated_at']
        );
    }


    public function loadUnitDropdown(){
        $id=DropdownCategory::insertGetId([
            "name" => "Rebate Unit",
            "slug" => "rebate_unit",
        ]);
        DropdownOption::insert([
            [
                "label" => "/Pip",
                "value" => "/pip",
                "dropdown_category_id" => $id
            ],
            [
                "label" => "/Lot",
                "value" => "/lot",
                "dropdown_category_id" => $id
            ],
            [
                "label" => "/%",
                "value" => "/%",
                "dropdown_category_id" => $id
            ],
        ]);
    }
    
}
