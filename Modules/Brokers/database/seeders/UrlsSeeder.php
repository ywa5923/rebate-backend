<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BatchImporter;
class UrlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/urls.csv');

         
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"urls",rowMapping:[
            "broker_id"=>1,
            "url_type"=>2,
            "name"=>3,
            "slug"=>4,
            "url"=>5,
            "option_category_id"=>6,

        ]);
      
        $importer->import(1,1);
        //broker_id,url_type,name,slug,url,option_category_id
        
    }
}
