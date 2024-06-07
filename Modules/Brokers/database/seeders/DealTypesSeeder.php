<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BatchImporter;

class DealTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->importDealTypes();
        $this->ImportDealtypesBrokers();
    }
    public function importDealTypes()
    {

        $csvFile = module_path('Brokers', 'database/seeders/csv/dealtypes.csv');
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"dealtypes",rowMapping:[
            "id"=>1,
            "code"=>2,
            "name"=>3,
            "description"=>4,
            "example"=>5
        ]);
      
      
        //id,code,name,description,example
        $importer->import(1,1);
    }

    public function ImportDealtypesBrokers()
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/brokers_dealtypes.csv');
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"broker_dealtype",rowMapping:[
            "broker_id"=>1,
            "dealtype_id"=>2
        ]);
      
      
      
        $importer->import(1,1);

    }
}
