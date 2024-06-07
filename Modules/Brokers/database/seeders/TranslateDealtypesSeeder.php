<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\DealType;
use Modules\Translations\Models\Translation;

class TranslateDealtypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/dealtypes_ro.csv');
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
                     "translationable_type"=>DealType::class,
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
