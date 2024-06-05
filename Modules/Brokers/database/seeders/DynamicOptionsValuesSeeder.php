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
                $optionsId = $this->getOptionsId($row);
                $options = $row;
                continue;
            }
            foreach ($row as $k => $v) {
                if ($k == 0) {
                    continue;
                }

                OptionValue::insert(
                    [
                        "broker_id" => $row[0],
                        "broker_option_id" => $optionsId[$k],
                        "option_slug" => $options[$k],
                        "value" => $v,
                        "status" => 1
                    ]
                );
            }
        }
    }
    public function getOptionsId($row)
    {
        $optionsId = [];
        foreach ($row as $k => $v) {
            if ($v !== 'broker_id') {
                $option = BrokerOption::where('slug', $v)->first();
                if ($option != null) {
                    $optionsId[$k] = $option->id;
                } else {
                    throw new \Exception("Option with slug {$v} not found");
                }
            }
        }
        return $optionsId;
    }
}
