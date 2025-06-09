<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\FormType;
use Modules\Brokers\Models\FormItem;
use Illuminate\Support\Facades\DB;

class FormTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create FormTypes
        $textType = FormType::create([
            "name" => "Text",
        ]);
        $numberType = FormType::create([
            "name" => "Number",
        ]);

        $numberWithSelectType = FormType::create([
            "name" => "NumberWithSelect"
        ]);

        // Create FormItems
        $textItem = FormItem::create([
            "name" => "Text",
            "placeholder" => "Enter Text",
            "type" => "text",
        ]);
        $numberItem = FormItem::create([
            "name" => "Number",
            "placeholder" => "Enter Number",
            "type" => "number",
        ]);
        $currencyItem = FormItem::create([
            "name" => "Currency",
            "placeholder" => "Select Currency",
            "type" => "select",
            "dropdown_id" => 2,
        ]);

        $unitItem = FormItem::create([
            "name" => "Unit",
            "type" => "select",
            "placeholder" => "Select Unit",
            "dropdown_id" => 1,
        ]);

        // Attach items using relationship
        $numberWithSelectType->items()->attach([
            $numberItem->id,
            $currencyItem->id
        ]);

        $textType->items()->attach([
            $textItem->id,
        ]);

        $numberType->items()->attach([
            $numberItem->id,
        ]);

        
    }

    public function loadData()
    {
        $id1 = FormType::insertGetId([
            "name" => "Text",
            "slug" => "text",
        ]);
        $id2 = FormType::insertGetId([
            "name" => "NumberWithSelect",
            "slug" => "number_with_select",
        ]);

        $itemId1 = FormItem::insertGetId([
            "name" => "Text",
            "type" => "text",

        ]);
        $itemId2 = FormItem::insertGetId([
            "name" => "Number",
            "type" => "number",
        ]);
        $itemId3 = FormItem::insertGetId([
            "name" => "Select Currency",
            "type" => "select",
            "dropdown_id" => 2,
        ]);

        DB::table('form_type_form_item')->insert([
            ['form_type_id' => $id1, 'form_item_id' => $itemId1],
        ]);
        // Attach items to NumberWithSelect form type
        DB::table('form_type_form_item')->insert([
            ['form_type_id' => $id2, 'form_item_id' => $itemId2],
            ['form_type_id' => $id2, 'form_item_id' => $itemId3],
        ]);
    }
}
