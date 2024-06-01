<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BatchImporter;

class DynamicOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/dynamic-options.csv');
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"broker_options",rowMapping:[
            "id"=>1,
            "name"=>2,
            "slug"=>3,
            "data_type"=>4,
            "form_type"=>5,
            "required"=>6,
            "position"=>7,
            "option_category_id"=>8,
            "for_brokers"=>9,
            "for_crypto"=>10,
            "for_props"=>11,
            "default_language"=>12,
        ]);
       //id,name,slug,data_type,form_type,required,position,option_category_id,for_brokers,for_crypto,for_props,default_language
        $importer->import(1,1);
    }
}
