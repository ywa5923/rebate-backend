<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerOption;
use Modules\Translations\Models\Translation;

class TranslateDynamicOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Translation::insert([
            [
                "translationable_type"=>BrokerOption::class,
                "translationable_id"=>29,
                "language_code"=>"ro",
                "property"=>'24h_support',
                "value"=>"Suport 24 ore",
                "translation_type"=>"property"
            ],
            [
                "translationable_type"=>BrokerOption::class,
                "translationable_id"=>39,
                "language_code"=>"ro",
                "property"=>'trading_instruments',
                "value"=>"Instrumente financiare",
                "translation_type"=>"property"
            ],
            [
                "translationable_type"=>BrokerOption::class,
                "translationable_id"=>44,
                "language_code"=>"ro",
                "property"=>'promotion_title',
                "value"=>"Titlu promotie",
                "translation_type"=>"property"
            ]

        ]);

        // Translation::insert([
        //     [
        //         "translationable_type"=>Broker::class,
        //         "translationable_id"=>null,
        //         "language_code"=>"ro",
        //         "property"=>'promotion_details',
        //          "metadata"=>json_encode([
        //             "support_options"=>"Optiuni de suport",
        //             "account_type"=>"Tipul contului",
        //             "trading_instrumets"=>"Instrumente de tranzactionare",
        //             "account_currencies"=>"Monedele contului",
        //             "trading_name"=>"Nume comercial",
        //             "overall_rating"=>"Rating general",
        //             'user_rating'=>"Rating utilizatori",
        //             'logo'=>'Sigla',
        //             'favicon'=>'Favicon',
        //             'home_url'=>'Link Acasa'


        //          ]),
        //         "translation_type"=>"columns"
        //     ]
           
        //]);
    }
}
