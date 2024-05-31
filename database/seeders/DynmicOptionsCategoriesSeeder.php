<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Brokers\Models\Broker;

class DynmicOptionsCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile=database_path("seeders/csv/categories.csv");

        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"dynamic_options_categories",rowMapping:[
            "id"=>0,
            "name"=>1
        ]);
      
        $importer->import();
    }
}
