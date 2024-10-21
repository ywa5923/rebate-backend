<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BatchImporter;
use Modules\Brokers\Models\BrokerOption;

class DynamicOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/default/broker_options.csv');
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"broker_options",rowMapping:[
            "id"=>1,
            "name"=>2,
            "slug"=>3,
            "data_type"=>4,
            "form_type"=>5,
            //"meta_data"=>6,
            "for_brokers"=>7,
            "for_crypto"=>8,
            "for_props"=>9,
            "required"=>10,
            "position"=>11,
            "default_language"=>12,
            "option_category_id"=>13
        
        ]);
       //id,name,slug,data_type,form_type,required,position,option_category_id,for_brokers,for_crypto,for_props,default_language
       //id,name,slug,data_type,form_type,meta_data,for_crypto,for_brokers,for_props,required,position,default_language,option_category_id
       $importer->import(1,1);
       
      $this->update();
    }

    public function update()
    {
        $default_loaded=["trading_name","logo","home_url","overall_rating","user_rating","account_currencies","trading_instruments"];
        
        foreach($default_loaded as $k=>$v){
            BrokerOption::where("slug",$v)->update([
                "default_loading"=>1,
                "default_loading_position"=>$k+1
            ]);
        }    
    }

    public function importData()
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/option_values.csv');
        $importer=new BatchImporter(filePath:$csvFile);
        $importer->setTableInfo(tableName:"option_values",rowMapping:[
         
        ]);

    }
}
