<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\Broker;
use Modules\Translations\Models\Translation;

class TranslateStaticBrokersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
    }

    public function translate()
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/brokers_ro.csv');
        $handle = fopen($csvFile, "r");
        $rowIndex=0;
        $columns=[];
        $translationRows=[];
        while (($row = fgetcsv($handle, 4096)) !== FALSE) {
            if($rowIndex==0)
            {
                $rowIndex++;
                $columns=$row;
            
                continue;
            }

            foreach($row as $k=>$v){

                if($k==0 || empty($v)){
                    //first element is broker_id, skip it
                    continue;
                }
                
                $translationRow=[
                     "translationable_type"=>Broker::class,
                    "translationable_id"=> $row[0],
                    "language_code"=>"ro",
                    "translation_type"=>"property",
                    "property"=>$columns[$k],
                    "value"=>$v
                ];
                // Translation::insert($translationRow);
                $translationRows[]= $translationRow;
            }
        }
        Translation::insert($translationRows);
    }
}
