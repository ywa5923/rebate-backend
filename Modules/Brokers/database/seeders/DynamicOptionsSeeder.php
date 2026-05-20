<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BatchImporter;
use Illuminate\Support\Facades\Log;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\DropdownCategory;

class DynamicOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/default/broker-options2026_v4.csv');
        $importer = new BatchImporter(filePath: $csvFile);
        $importer->setTableInfo(tableName: "broker_options", rowMapping: [
            "id" => 1,
            "name" => 2,
            "slug" => 3,
            "applicable_for" => 4,
            "data_type" => 5,
            "form_type" => 6,
            "tooltip" => 8,
            "meta_data" => null,
            "for_crypto" => 10,
            "for_brokers" => 11,

            "for_props" => 12,
            "required" => 13,
            "position" => 14,
            "default_language" => 15,
            "option_category_id" => 16,
            "dropdown_category_id" => 17,
            "placeholder" => 18,

        ]);
        //id,name,slug,applicable_for,data_type,form_type,is_public,tooltip,metadata,
        //for_crypto,for_brokers,for_props,required,position,default_language
        //option_category_id,dropdown_category_id,placeholder,created_at,updated_at

        $importer->setRowTransformer(function (array $record, array $csvRow): array {
            $recordDropDownCat_Id = $record['dropdown_category_id'] === "NULL" ? null : $record['dropdown_category_id'];
            if (!empty($recordDropDownCat_Id) && is_string($recordDropDownCat_Id) && !is_numeric($recordDropDownCat_Id)) {
                $recordDropDownCat = DropdownCategory::where("slug", $recordDropDownCat_Id)->first();
                if ($recordDropDownCat) {
                    $recordDropDownCat_Id = $recordDropDownCat->id;
                } else {
                    $recordDropDownCat_Id = null;
                    $record['meta_data'] = json_encode(["error" => "doesnt find the list with slug: " . $record['dropdown_category_id']]);
                    //Log::error("doesnt find the list with slug: ".$recordDropDownCat_Id);
                }
            }
            $record['dropdown_category_id'] = $recordDropDownCat_Id;


            //change "NULL" to null in the record
            $record = array_map(function ($value) use ($record) {
                if (is_string($value) && $value === "NULL") {
                    return null;
                }
                return $value;
            }, $record);
            return $record;
        });
        $importer->import(1);



        // $default_loaded=["trading_name","logo","home_url","overall_rating","user_rating","account_currencies","trading_instruments"];
        $default_loaded = ["trading_name", "logo", "home_url", "account_currencies"];
        foreach ($default_loaded as $k => $v) {
            BrokerOption::where("slug", $v)->update([
                "default_loading" => 1,
                "default_loading_position" => $k + 1
            ]);
        }
    }
}
