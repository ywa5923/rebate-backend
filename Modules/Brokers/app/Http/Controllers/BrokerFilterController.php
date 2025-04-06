<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Repositories\BrokerOptionInterface;
use Modules\Brokers\Repositories\CompanyRepository;
use Modules\Brokers\Repositories\CompanyUniqueListInterface;
use Modules\Brokers\Repositories\FilterRepository;
use Modules\Brokers\Repositories\OptionValueRepository;
use Modules\Brokers\Repositories\RegulatorRepository;
use Modules\Brokers\Services\BrokerFilterQueryParser;
use Modules\Brokers\Transformers\SettingCollection;
use PHPUnit\Util\Filter;
use Modules\Brokers\Models\Zone;

class BrokerFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BrokerFilterQueryParser $queryParser, Request $request)
    {
        $queryParser->parse($request);
        $zonecondition=$queryParser->getWhereParam("zone");
       
        //to be change to the real zone
        $zonecondition[2]="zone1";
        $languageCondition = $queryParser->getWhereParam("language");
        if(empty($languageCondition) || empty($zonecondition)){
            //throw new \Exception("country or language not found");
            return response()->json(['error' => 'Zone and language parameters are required'], 422);
        }
       
        $companyRepo = new CompanyRepository();
        $regulatorRepo = new RegulatorRepository();
        $optionsValuesRepo = new OptionValueRepository();
        $filterRepo = new FilterRepository();
        //$currencies = $filterRepo->getBrokerCurrencyList();
       
        //get translated names for filter's names
        $filterNames=$filterRepo->getSettingsParam("page_brokers",$languageCondition)["filters"];

        $currencies=$optionsValuesRepo->getUniqueList($languageCondition, BrokerOptionInterface::ACCOUNT_CURRENCIES,  $zonecondition);
        
        $mobilePlatforms=$optionsValuesRepo->getUniqueList($languageCondition, BrokerOptionInterface::MOBILE_PLATFORM_LINK,  $zonecondition);
        $webPlatforms=$optionsValuesRepo->getUniqueList($languageCondition, BrokerOptionInterface::WEB_PLATFORM_LINK,  $zonecondition);

        //$tradingInstruments = $filterRepo->getBrokerStaticFieldList($language, 'trading_instruments');
        $tradingInstruments = $optionsValuesRepo->getUniqueList($languageCondition, BrokerOptionInterface::TRADING_INSTRUMENTS,  $zonecondition);

        //$supportOptions = $filterRepo->getBrokerStaticFieldList($language, 'support_options');
        $supportOptions = $optionsValuesRepo->getUniqueList($languageCondition, BrokerOptionInterface::SUPPORT_OPTIONS,  $zonecondition);


        $officesList = $companyRepo->getUniqueList($languageCondition, CompanyUniqueListInterface::OFFICES);

        $headquartersList = $companyRepo->getUniqueList($languageCondition, CompanyUniqueListInterface::HEADQUARTERS);
        $regulatorsList = $regulatorRepo->getUniqueList($languageCondition);

        $withdrawalMethods = $optionsValuesRepo->getUniqueList($languageCondition, BrokerOptionInterface::WITHDRAWAL_METHODS,  $zonecondition);
        
        return  [

            "simple_filters" => [
                [
                    "field"=>"filter_popularity",
                    "name"=>$filterNames["client_popularity"],
                    "type"=>"rating",
                    "expanded"=>true,
                    "options"=>[
                        [
                            "name" => "5.0",
                            "value" => "5"
                        ],
                        [
                            "name" => "4.0-5.0",
                            "value" => "4"
                        ],
                        [
                            "name" => "3.0-4.0",
                            "value" => "3"
                        ],
                        [
                            "name" => "2.0-3.0",
                            "value" => "2"
                        ],
                        
                        [
                            "name" => "1.0-2.0",
                            "value" => "1"
                        ]
                       
                    ]
    
                ],
                [
                "field"=>"filter_regulator_rating",
                "name"=>$filterNames["regulator_rating"],
                "type"=>"rating",
                "expanded"=>true,
                
                "options"=>[
                        [
                            "name" => "5.0",
                            "value" => "5"
                        ],
                        [
                            "name" => "4.0-5.0",
                            "value" => "4"
                        ],
                        [
                            "name" => "3.0-4.0",
                            "value" => "3"
                        ],
                        [
                            "name" => "2.0-3.0",
                            "value" => "2"
                        ],
                        
                        [
                            "name" => "1.0-2.0",
                            "value" => "1"
                        ]
                       
                    ]
    
               ]

            ],
            "advanced_filters" => [
                [
                    "field" => "filter_offices",
                    "name"=>$filterNames["offices"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => $this->transform($officesList)
                ],
                [
                    "field" => "filter_headquarters",
                    "name"=>$filterNames["headquarters"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => $this->transform($headquartersList)
                ],
                [
                    "field" => "filter_regulators",
                    "name"=>$filterNames["regulators"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => $this->transform($regulatorsList)
                ],
                [
                    "field" => "filter_withdrawal_methods",
                    "name"=>$filterNames["withdrawal_methods"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => $this->transform($withdrawalMethods)
                ],
                [
                    "field" => "filter_min_deposit",
                    "name"=>$filterNames["min_deposit"],
                    "type" => "radio",
                    "expanded"=>false,
                    "options" => [
                        [
                            "name" => "< 100",
                            "value" => "lt100"
                        ],
                        [
                            "name" => "< 200",
                            "value" => "lt200"
                        ],
                        [
                            "name" => "< 500",
                            "value" => "lt500"
                        ],
                        [
                            "name" => "< 1000",
                            "value" => "lt1000"
                        ]
                    ]
                ],
                [
                    "field" => "filter_group_trading_account_info",
                    "name"=>$filterNames["group_trading_account_info"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => [
                        [
                            "name" => $filterNames["islamic_accounts"],
                            "value" => "islamic_accounts"
                        ],
                        [
                            "name" => $filterNames["1_click_trading"],
                            "value" => "1_click_trading"
                        ],
                        [
                            "name" => $filterNames["trailing_stops"],
                            "value" => "trailing_stops"
                        ],
                        [
                            "name" => $filterNames["allow_scalping"],
                            "value" => "allow_scalping"
                        ],
                        [
                            "name" => $filterNames["allow_hedging"],
                            "value" => "allow_hedging"
                        ],
                        [
                            "name" => $filterNames['non-expiring_demo_accounts'],
                            "value" => 'non-expiring_demo_accounts'
                        ],
                        [
                            "name" => $filterNames['trading_api'],
                            "value" => 'trading_api'
                        ],
                        [
                            "name" => $filterNames['allow_news_trading'],
                            "value" => 'allow_news_trading'
                        ],
                        [
                            "name" => $filterNames['allow_expert_advisors'],
                            "value" => 'allow_expert_advisors'
                        ],
                        [
                            "name" => $filterNames['copy_trading'],
                            "value" => 'copy_trading'
                        ],
                        [
                            "name" => $filterNames['segregated_accounts'],
                            "value" => 'segregated_accounts'
                        ],
                        [
                            "name" => $filterNames['interest_on_free_margin'],
                            "value" => 'interest_on_free_margin'
                        ],
                        [
                            "name" => $filterNames['free_vps'],
                            "value" => 'free_vps'
                        ]
                    ]
                ],
                [
                    "field" => "filter_group_spread_types",
                    "name"=>$filterNames["group_spread_types"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => [[
                        "name" => $filterNames['fixed_spreads'],
                        "value" => 'fixed_spreads'
                    ]]
                ],
                [
                    "field" => "filter_group_fund_managers_features",
                    "name"=>$filterNames["group_fund_managers_features"],
                    "type" => "checkbox",
                    "expanded"=>true,
                    "headless"=>true,
                    "options" => [
                        [
                        "name" => $filterNames['mam_pamm_platforms'],
                        "value" => 'mam_pamm_platforms'
                    ],
                    [
                        "name"=>$filterNames["mam_pamm_leaderboards"],
                        "value"=>"mam_pamm_leaderboards"
                    ],
                    [
                        "name"=>$filterNames["managed_accounts"],
                        "value"=>"managed_accounts"
                    ]
                    ]
                ],
    
                [
                    "field" => "filter_account_currency",
                    "name"=>$filterNames["account_currency"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => $this->transform($currencies, false)
                ],
                [
                    "field" => "filter_trading_instruments",
                    "name"=>$filterNames["trading_instruments"],
                    "type" => "checkbox",
                    "options" => $this->transform($tradingInstruments)
                ],
                [
                    "field" => "filter_support_options",
                    "name"=>$filterNames["support_options"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => $this->transform($supportOptions)
                ],
                [
                    "field" => "filter_mobile",
                    "name"=>$filterNames["mobile_platform_link"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => $this->transform( $mobilePlatforms)
                ],
                [
                    "field" => "filter_web",
                    "name"=>$filterNames["web_platform_link"],
                    "type" => "checkbox",
                    "expanded"=>false,
                    "options" => $this->transform( $webPlatforms)
                ],
    

            ]
           
           
           
        ];
    }

    public function transform(array $data, $isAssociative = true)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[] = ($isAssociative) ? (["name" => $value, "value" => $key]) : (["name" => $value, "value" => $value]);
        }

     usort($result,fn($a,$b)=>$a["value"]<=>$b["value"]);
        return $result;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('brokers::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('brokers::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('brokers::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
