<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\Company;
use Modules\Translations\Models\Translation;

class TranslateCompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/companies_ro.csv');
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

            $brokerId=$row[0];
           // dd(  $brokerId);
            //get company Id 
           // $companyId= Broker::find($brokerId)->companies()->first()->id;

             dd($companyId= Broker::find($brokerId)->companies()->first());
         
            foreach($row as $k=>$v){

                if($k==0 || empty($v)){
                    //first element is broker_id, skip it
                    continue;
                }
                $translationRow=[
                     "translationable_type"=>Company::class,
                    "translationable_id"=> $companyId,
                    "language_code"=>"ro",
                    "translation_type"=>"property",
                    "property"=>$columns[$k],
                    "value"=>$v
                ];
                Translation::insert($translationRow);

               // $translationRows[]= $translationRow;
            }
        }
        //Translation::insert($translationRows);
    }
}
// // $this->info("\\\\///...translating companies ");
        //$this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\TranslateCompaniesSeeder"]);