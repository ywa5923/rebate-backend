<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class DynamicOptions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
       $csvFile=database_path("seeders/csv/test.csv");

        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"dynamic",rowMapping:[
            "title"=>1,
            "content"=>2,
            "data"=>0
        ]);
      
        $importer->import();
        
    }
}
