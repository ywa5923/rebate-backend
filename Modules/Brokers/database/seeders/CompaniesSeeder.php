<?php

namespace Modules\Brokers\Database\Seeders;
use Database\Seeders\BatchImporter;


use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/companies.csv');

         
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"companies",rowMapping:[
            "id"=>1,
            "broker_id"=>2,
            "name"=>3,
            "licence_number"=>4,
            "year_founded"=>5,
            "employees"=>6,
            "headquarters"=>7,
            "description"=>8,
            "offices"=>9
        ]);
      
      
        //company_id,broker_id,name,licence_number,year_founded,employees,headquartes,description,offices
        $importer->import(1);

        // $importer->setTableInfo(tableName:"broker_company",rowMapping:[
        //     "company_id"=>1,
        //     "broker_id"=>2
        // ]);
        // $importer->import(1);
    }
}
