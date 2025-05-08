<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;


use Modules\Translations\Models\Country;
use Modules\Translations\Models\Zone;


class ZonesSeeder extends Seeder
{

    //php artisan db:seed --class=\\Modules\\Brokers\\Database\\Seeders\\SettingsSeeder
    public function run(){
        Zone::insert([
            [
                "id"=>1,
                "name" => "Europe",
                "zone_code" => "eu",
               
            ],
            [
                "id"=>2,
                "name" => "Asia",
                "zone_code" => "as",
               
            ]
            
            ]);

            Country::insert([
                [
                    "id"=>1,
                    "name" => "Romania",
                    "country_code" => "ro",
                    "zone_id" => 1
                ],
                [
                    "id"=>2,
                    "name" => "Japan",
                    "country_code" => "jp",
                    "zone_id" => 2
                ]
                
                ]);
    }
}