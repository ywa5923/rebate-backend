<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;

use Modules\Brokers\Models\Setting;
use Modules\Brokers\Models\Zone;
use Modules\Translations\Models\Translation;

class ZonesSeeder extends Seeder
{

    //php artisan db:seed --class=\\Modules\\Brokers\\Database\\Seeders\\SettingsSeeder
    public function run(){
        Zone::insert([
            [
                "id"=>1,
                "name" => "Europe",
                "zone_code" => "eu",
                "countries" => "ro,pl,bg,hu,sk,cs,lv,lt,ee,de,fr,es,it,pt,ru,ua,se"
            ],
            [
                "id"=>2,
                "name" => "Asia",
                "zone_code" => "as",
                "countries" => "ch"
            ]
            
            ]);
    }
}