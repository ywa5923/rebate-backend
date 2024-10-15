<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;

use Modules\Brokers\Models\Setting;
use Modules\Translations\Models\Translation;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settingId=Setting::insertGetId([
            "key"=>"page_brokers",
            "value"=>json_encode($this->getEnData())
        ]);
        
   
    Translation::insert([
        "translationable_type"=>Setting::class,
        "translationable_id"=> $settingId,
        "language_code"=>"ro",
        "translation_type"=>"property",
        "property"=> "page_brokers",
        "value"=>json_encode($this->getRoData())
    ]);

    }

    public function getRoData()
    {
        return [
        
            'broker_ext_columns'=>[
                "regulators"=>"Regulatori"
            ],
            'filters'=>[
                'filter_offices'=>'Sedii',
                'filter_headquarters'=>'Sedii Centrale',
                'filter_regulators'=>'Regulatori',
                "filter_withdrawal_methods"=>"Metode de Retragere",
                "filter_min_deposit"=>"Depozit Min.",
                "filter_group_trading_account_info"=>"Info cont tranzactie",
                "filter_group_spread_types"=>"Tipuri de Spreaduri",
                "filter_group_fund_managers_features"=>"Optiuni Administrare de Fonduri",
                "filter_account_currency"=>"Moneda Cont",
                "filter_trading_instruments"=>"Platforme de Tranzationare",
                "filter_support_options"=>"Optiuni Suport"
               
            ]
            ];

    }

    public function getEnData()
    {
        return [
        
            'broker_ext_columns'=>[
                "regulators"=>"Regulators"
            ],
            'filters'=>[
                'filter_offices'=>'Offices',
                'filter_headquarters'=>'Headquarters',
                'filter_regulators'=>'Regulators',
                "filter_withdrawal_methods"=>"Withdrawal Methods",
                "filter_min_deposit"=>"Min Deposit",
                "filter_group_trading_account_info"=>"Trading Account Info",
                "filter_group_spread_types"=>"Spread Types",
                "filter_group_fund_managers_features"=>"Fund Managers Features",
                "filter_account_currency"=>"Account Currency",
                "filter_trading_instruments"=>"Trading Instruments",
                "filter_support_options"=>"Support Options"
               
            ]
            ];
    }
}
