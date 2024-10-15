<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionValue;

class DynamicOptionsValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rowIndex = 0;

        $csvFile = module_path('Brokers', 'database/seeders/csv/dynamic_options_values.csv');
        $handle = fopen($csvFile, "r");

        while (($row = fgetcsv($handle, 4096)) !== FALSE) {

            if ($rowIndex === 0) {
                //first row is with options name, get the id for every option name and keep in array to store in options value table
                $rowIndex++;
                $optionsInfo = $this->getOptionsInfo($row);

                $slugs = $row;
                continue;
            }
            foreach ($row as $k => $v) {
                if ($k == 0 || empty($v)) {
                    continue;
                }
                [$brokerOptionId,$optionFormType]=$optionsInfo[$k];
              

                if($optionFormType=="Link")
                {
                   $this->parseAndInsertLinkType($v,$row[0],$brokerOptionId,$slugs[$k]);
                }else{

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
                    OptionValue::insert(
                        [
                            "broker_id" => $row[0],
                            "broker_option_id" => $brokerOptionId,
                            "option_slug" => $slugs[$k],
                            "value" => $value,
                            "status" => 1,
                            "metadata"=>$metadata
                        ]
                    );
                }
               

            }
        }
    }

    public function parseAndInsertLinkType($data,$brokerId,$brokerOptionId,$slug)
    {
        $foundMatch=preg_match_all('|<a[^>]*href="(.+)"[^>]*>(.+)</[^>]*a[^>]*>|U',$data,$out,PREG_PATTERN_ORDER);
        if($foundMatch){
            foreach($out[1] as $i=>$url)
            {
                OptionValue::insert(
                    [
                        "broker_id" => $brokerId,
                        "broker_option_id" => $brokerOptionId,
                        "option_slug" => $slug,
                        "value" => $out[2][$i],
                        "metadata"=>json_encode(["url"=>$url]),
                        "status" => 1
                    ]
                );
          
            }
        }else{
            OptionValue::insert(
                [
                    "broker_id" =>  $brokerId,
                    "broker_option_id" => $brokerOptionId,
                    "option_slug" => $slug,
                    "value" => $data,
                    "status" => 1
                ]
            );
        }
    }
    public function getOptionsInfo($row)
    {
        $optionsInfo = [];
        foreach ($row as $k => $v) {
            if ($v !== 'broker_id') {
                
                $option = BrokerOption::where('slug', $v)->first();
                if ($option != null) {
                    $optionsInfo[$k] = [$option->id,$option->form_type];
                } else {
                    throw new \Exception("Option with slug {$v} not found");
                }
            }
        }
        return  $optionsInfo;
    }
}
