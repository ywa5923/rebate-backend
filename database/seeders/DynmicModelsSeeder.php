<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DynmicModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile=database_path("seeders/csv/dynamic_models.csv");

        
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"dynamic_models",rowMapping:[
            "id"=>0,
            "model"=>1
        ]);
      
        $importer->import();
    }
    
}
