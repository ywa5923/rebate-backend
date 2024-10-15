<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\OptionValue;
use Modules\Translations\Models\Translation;
use Modules\Brokers\Models\BrokerOption;
class TranslateDynamicOptionsValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/dynamic_options_values_ro.csv');
        $this->translate($csvFile,"ro");

        $csvFile = module_path('Brokers', 'database/seeders/csv/dynamic_options_values.csv');
        $this->translate($csvFile,"en");

    }

    public function translate(string $csvFile,$language):void
    {
        $handle = fopen($csvFile, "r");
        $rowIndex=0;
        $optionSlugs=[];
        $translationRows=[];
        while (($row = fgetcsv($handle, 4096)) !== FALSE) {

            if ($rowIndex === 0) {
                //first row is with options name, get the id for every option name and keep in array to store in options value table
                $rowIndex++;
                $optionSlugs = $row;
                $optionsInfo = $this->getOptionsInfo($row);
                continue;
            }
  
            foreach($row as $k=>$v){
               
                if($k==0 || empty($v)){
                    //first element is broker_id, skip it
                    continue;
                }
                $brokerId=$row[0];
                $optionSlug=$optionSlugs[$k];
                $optionFormType=$optionsInfo[$k];

                if($optionFormType=="Link")
               
                {
                    $parsedLinks=$this->parseLinks($v);
                    
                    $translateLinks=[];
                    if(is_array($parsedLinks))
                    {
                        foreach($parsedLinks as $url=>$text)
                        {

                            $optionValueId=$this->getOptionValueId([
                                ['option_slug', $optionSlug],
                                 ['broker_id', $brokerId],
                                 ['metadata->url',$url]
                            ]);
                            if($optionValueId==null){
                                continue;
                               //var_dump($optionSlug,$brokerId,$url);
                            }
                            $translateLinks[]=[
                                "translationable_type"=>OptionValue::class,
                                "translationable_id"=>  $optionValueId,
                                "language_code"=>$language,
                                "translation_type"=>"property",
                                "property"=> $optionSlug,
                                "value"=>$text
                               ];
                            
                        }
                    }else{
                        
                        $optionValueId=$this->getOptionValueId([
                            ['option_slug', $optionSlug],
                            ['broker_id', $brokerId]
                        ]);
                        if($optionValueId==null){
                           // continue;
                        }
                        $translateLinks[]=[
                            "translationable_type"=>OptionValue::class,
                            "translationable_id"=>  $optionValueId,
                            "language_code"=>$language,
                            "translation_type"=>"property",
                            "property"=> $optionSlug,
                            "value"=>$parsedLinks
                           ];
                    }

                    Translation::insert( $translateLinks);
                }else{

                    //translate normally
                    $optionValueObj=OptionValue::where([
                        ['option_slug', $optionSlug],
                         ['broker_id', $brokerId]
                        ])->first();
    
                      
                    if($optionValueObj!=null){

                        $value=$v;
                        $metadata=null;

                       if( $optionFormType=="numberWithUnit")
                       {
                        $found=preg_match_all("/^(\d+(?:\.\d+)?)\s*(.+)$/", $v, $matches);
                          if($found){
                             $quantity = $matches[1][0];
                             $unit = trim($matches[2][0]);
                             $value=$quantity;
                             $metadata=json_encode(["unit"=>$unit]);
                         }
                       }
                       $translationRow=[
                        "translationable_type"=>OptionValue::class,
                        "translationable_id"=>  $optionValueObj->id,
                        "language_code"=>$language,
                        "translation_type"=>"property",
                        "property"=> $optionSlug,
                        "value"=>$value,
                        "metadata"=>$metadata
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
                        var_dump($k,$optionSlug,$brokerId); 
                    }   
                }//end else

                
                
                //find option value id
               
                
            }//end for

            $rowIndex++;
        }
       
        Translation::insert($translationRows);
    }


    public function getOptionValueId($whereConditions)
    {
        $optionValueObj=OptionValue::where($whereConditions)->first();
        return $optionValueObj?$optionValueObj->id:null;
    }
    /**
     * Takes a string which may contain links in the form of <a href="link">text</a> and
     * returns an array where the keys are the links and the values are the text of the links.
     * If the string does not contain any links, the original string is returned.
     * @param string $data
     * @return array|string
     */

    public function parseLinks($data):array|string
    {
        $urls=[];
        $foundMatch=preg_match_all('|<a[^>]*href="(.+)"[^>]*>(.+)</[^>]*a[^>]*>|U',$data,$out,PREG_PATTERN_ORDER);
        if($foundMatch){
            foreach($out[1] as $i=>$url)
            {
                //an array where the keys are the links and the values are the text of the links
                $urls[$url]=$out[2][$i];
            
            }
            return $urls;
        }else{
            return $data;
        }
    }


        /**
         * Takes a row from the csv and returns an array of form_types for each option slug in the row
         * @param array $row
         * @return array
         * @throws \Exception
         */

    public function getOptionsInfo($row)
    {
        $optionsInfo = [];
        foreach ($row as $k => $v) {
            if ($k == 0) {
                continue;
            }
                
                $option = BrokerOption::where('slug', $v)->first();
                if ($option != null) {
                    $optionsInfo[$k] = $option->form_type;
                } else {
                    throw new \Exception("Option with slug {$v} not found");
                }
            
        }
        return  $optionsInfo;
    }
}