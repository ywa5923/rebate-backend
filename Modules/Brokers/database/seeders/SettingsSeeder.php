<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;

use Modules\Brokers\Models\Setting;
use Modules\Translations\Models\Translation;

class SettingsSeeder extends Seeder
{
    //php artisan db:seed --class=\\Modules\\Brokers\\Database\\Seeders\\SettingsSeeder
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
                'offices'=>'Sedii',
                'headquarters'=>'Sedii Centrale',
                'regulators'=>'Regulatori',
                "withdrawal_methods"=>"Metode de Retragere",
                "min_deposit"=>"Depozit Min.",
                "group_trading_account_info"=>"Info cont tranzactie",
                "group_spread_types"=>"Tipuri de Spreaduri",
                "group_fund_managers_features"=>"Optiuni Administrare de Fonduri",
                "account_currency"=>"Moneda Cont",
                "trading_instruments"=>"Platforme de Tranzationare",
                "support_options"=>"Optiuni Suport",
                "islamic_accounts"=>"Conturi Islamice",
                "1_click_trading"=>"Tranzacționarea cu un Singur Click",
                "trailing_stops"=>"Trailling Stops",
                "allow_scalping" => "Permiterea Scalpingului",
                "allow_hedging"=> "Permiterea Hedgingului",
                'non-expiring_demo_accounts' => "Conturi Demo Fără Termen de Expirare",
                'trading_api' => "Trading API",
                'allow_news_trading'=> "Permite News Trading",
                'allow_expert_advisors'=>"Acceptă Expert Advisors",
                'copy_trading'=>"Copiere Tranzacții",
                'segregated_accounts'=> "Conturi Individuale",
                'interest_on_free_margin'=> "Dobândă pe Marja Liberă",
                'free_vps'=> "VPS (Server Virtual Privat)",
                'mam_pamm_platforms' => "Platforme MAM/PAMM",
                "mam_pamm_leaderboards"=>"Platforme Principale MAM/PAMM",
                "managed_accounts"=>"Conturi Administrate",
                'fixed_spreads'=>"Spread-uri Fixe",
                "mobile_platform_link"=>"Platforme de Mobil",
                "web_platform_link"=>"Platforme Web",
                "client_popularity"=>"Popularitate Client",
                "regulator_rating"=> "Rating Regulator",

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
                'offices'=>'Offices',
                'headquarters'=>'Headquarters',
                'regulators'=>'Regulators',
                "withdrawal_methods"=>"Withdrawal Methods",
                "min_deposit"=>"Min Deposit",
                "group_trading_account_info"=>"Trading Account Info",
                "group_spread_types"=>"Spread Types",
                "group_fund_managers_features"=>"Fund Managers Features",
                "account_currency"=>"Account Currency",
                "trading_instruments"=>"Trading Instruments",
                "support_options"=>"Support Options",
                "islamic_accounts"=>"Islamic Accounts",
                "1_click_trading"=>"One Click Trading",
                "trailing_stops"=>"Trailling Stops",
                "allow_scalping" => "Allow Scalping",
                "allow_hedging"=> "Allow Hedging",
                'non-expiring_demo_accounts' => "Demo accounts",
                'trading_api' => "Trading API",
                'allow_news_trading'=> "Allow News Trading",
                'allow_expert_advisors'=>"Allow Expert Advisors",
                'copy_trading'=>"Copy Trading",
                'segregated_accounts'=> "Segregated Accounts",
                'interest_on_free_margin'=> "Interest on Free Margin",
                'free_vps'=> "VPS",
                'mam_pamm_platforms' => "MAM/PAMM Platforms",
                "mam_pamm_leaderboards"=>"MAM/PAMM Leaderboards",
                "managed_accounts"=>"Managed Accounts",
                'fixed_spreads'=>"Fixed Spreads",
                "mobile_platform_link"=>"Mobile Platforms",
                "web_platform_link"=>"Web Platforms",
                "client_popularity"=>"Client Popularity",
                "regulator_rating"=> "Regulator Rating"
               
            ]
            ];
    }
}
//   //{"options":["notes","min_deposit","min_trade_size"],"relations":"regulators"}