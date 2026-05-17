<?php

namespace Modules\Brokers\Database\Seeders;

use Database\Seeders\BatchImporter;
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
        // $this->loadCurrencyDropdown();
        $this->loadAllLists();
    }

    public function loadAllLists()
    {
        //scan the  csv/lists folder and get all csv files

        $files = glob(module_path('Brokers', 'database/seeders/csv/lists/*.csv'));
        foreach ($files as $file) {
            $fileName = basename($file);
            $dropdownCategorySlug = str_replace('.csv', '', $fileName);
            $dropdownCategoryName = ucfirst(str_replace('_', ' ', $fileName));
            $dropDownCategoryId = DropdownCategory::insertGetId([
                'name' => $dropdownCategoryName,
                'slug' => $dropdownCategorySlug,
            ]);
            $this->loadList($file, $dropDownCategoryId);
        }
    }

    public function loadList(string $file, int $dropDownCategoryId)
    {
        $importer = new BatchImporter(filePath: $file);
        $importer->setTableInfo(tableName: 'dropdown_options', rowMapping: [
            'label' => 1,
            'value' => 2,
            'order' => 3,
            'dropdown_category_id' => -$dropDownCategoryId,
        ]);
        $importer->import(1, 1);
    }

    public function loadCurrencyDropdown()
    {
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

    public function loadUnitDropdown()
    {
        $id = DropdownCategory::insertGetId([
            'name' => 'Rebate Unit',
            'slug' => 'rebate_unit',
        ]);
        DropdownOption::insert([
            [
                'label' => '/Pip',
                'value' => '/pip',
                'dropdown_category_id' => $id,
            ],
            [
                'label' => '/Lot',
                'value' => '/lot',
                'dropdown_category_id' => $id,
            ],
            [
                'label' => '/%',
                'value' => '/%',
                'dropdown_category_id' => $id,
            ],
        ]);
    }
}
