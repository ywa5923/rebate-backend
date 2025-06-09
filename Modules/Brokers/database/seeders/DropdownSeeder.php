<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\DropdownCategory;
use Modules\Brokers\Models\DropdownOption;
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
        $id=DropdownCategory::insertGetId([
            "name" => "Currency",
            "slug" => "currency",
        ]);
        DropdownOption::insert([
           [
            "label" => "USD",
            "value" => "usd",
            "dropdown_category_id" => $id
           ],
           [
            "label" => "EUR",
            "value" => "eur",
            "dropdown_category_id" => $id
           ],
           [
            "label" => "GBP",
            "value" => "gbp",
            "dropdown_category_id" => $id
           ],
           [
            "label" => "CHF",
            "value" => "chf",
            "dropdown_category_id" => $id
           ],
           [
            "label" => "JPY",
            "value" => "jpy",
            "dropdown_category_id" => $id
           ],
           [
            "label" => "CAD",
            "value" => "cad",
            "dropdown_category_id" => $id
           ],
           
           
        ]);
    }


    public function loadUnitDropdown(){
        $id=DropdownCategory::insertGetId([
            "name" => "Unit",
            "slug" => "unit",
        ]);
        DropdownOption::insert([
            [
                "label" => "Unit",
                "value" => "unit",
                "dropdown_category_id" => $id
            ],
            [
                "label" => "Lots",
                "value" => "lots",
                "dropdown_category_id" => $id
            ],
        ]);
    }
    
}
