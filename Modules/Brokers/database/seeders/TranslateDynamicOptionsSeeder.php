<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\OptionValue;
use Modules\Translations\Models\Translation;

class TranslateDynamicOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/dynamic_options_values_ro.csv');
        $handle = fopen($csvFile, "r");
        $rowIndex=0;
        $optionSlugs=[];
        $translationRows=[];
        while (($row = fgetcsv($handle, 4096)) !== FALSE) {

            if ($rowIndex === 0) {
                //first row is with options name, get the id for every option name and keep in array to store in options value table
                $rowIndex++;
                $optionSlugs = $row;
                continue;
            }
  
            foreach($row as $k=>$v){
               
                if($k==0 || empty($v)){
                    //first element is broker_id, skip it
                    continue;
                }
                $brokerId=$row[0];
                $optionSlug=$optionSlugs[$k];
                //find option value id
                $optionValueObj=OptionValue::where([
                    ['option_slug', $optionSlug],
                     ['broker_id', $brokerId]
                    ])->first();

                  
                if($optionValueObj!=null){
                   $translationRow=[
                    "translationable_type"=>OptionValue::class,
                    "translationable_id"=>  $optionValueObj->id,
                    "language_code"=>"ro",
                    "translation_type"=>"property",
                    "property"=> $optionSlug,
                    "value"=>$v
                   ];

                //Translation::insert($translationRow);
                $translationRows[]=$translationRow;
                if($rowIndex % 50==0)
                {
                    Translation::insert($translationRows);
                 
                    //remove all elements from array
                    array_splice($translationRows,0,count($translationRows));
                   
                }

               
                }else{
                    dd($optionSlugs,$k,$optionSlug,$brokerId); 
                }   
                
            }

            $rowIndex++;
        }
       
        Translation::insert($translationRows);
      echo count($translationRows);

    }
}