<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BatchImporter;

class OptionsCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         //$this->call();
        
         $csvFile = module_path('Brokers', 'database/seeders/csv/default/categories.csv');

         
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"option_categories",rowMapping:[
            "id"=>1,
            "name"=>2,
            "slug"=>3,
            "position"=>4
        ]);
      
        $importer->import();
        
    }
}
