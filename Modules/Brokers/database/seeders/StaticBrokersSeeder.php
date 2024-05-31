<?php

namespace Modules\Brokers\Database\Seeders;

use Database\Seeders\BatchImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class StaticBrokersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('broker_types')->insert([
            [
                "id" => 1,
                "name" => "Brokers",
                "default_language" => "en"
            ],
            [
                "id" => 2,
                "name" => "Crypto",
                "default_language" => "en"
            ],
            [
                "id" => 3,
                "name" => "Prop Firms",
                "default_language" => "en"
            ]
        ]);
        
        $csvFile = module_path('Brokers', 'database/seeders/csv/brokers.csv');
        $importer = new BatchImporter(filePath: $csvFile);
        $importer->setTableInfo(tableName: "brokers", rowMapping: [
            "id" => 1,
            "logo" => 2,
            "trading_name" => 3,
            "user_rating" => 4,
            "account_currencies" => 5,
            "broker_type_id" => 6,
            "support_options" => 7,
            "account_type" => 8,
            "trading_instruments" => 9,
            "language" => 10,
            "default_language" => 11

        ]);
        // id,logo,trading_name,user_rating,account_currencies,broker_type_id,support_options,account_type,trading_instruments,language,default_language

        $importer->import(1,1);
    }
}
