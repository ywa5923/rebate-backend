<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BatchImporter;

class RegulatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->importRegulators();
        $this->importBrokersRegulators();

        
    }

    public function importRegulators()
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/regulators.csv');
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"regulators",rowMapping:[
            "id"=>1,
            "abreviation"=>2,
            "rating"=>3,
            "capitalization"=>4,
            "enforced"=>5,
            "website"=>6,
            "name"=>7,
            "country"=>8,
            "description"=>9

        ]);
       // id,abreviation,rating,capitalization,enforced,website,name,country,description
        $importer->import(1);
    }

    public function importBrokersRegulators()
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/brokers_regulators.csv');

         
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"broker_regulator",rowMapping:[
            "broker_id"=>1,
            "regulator_id"=>2
        ]);
      //broker_id,regulator_id
        $importer->import(1,1);

    }
}
