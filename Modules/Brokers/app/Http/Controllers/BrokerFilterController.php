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
use PHPUnit\Util\Filter;

class BrokerFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BrokerFilterQueryParser $queryParser, Request $request)
    {
        $queryParser->parse($request);
        $language = $queryParser->getWhereParam("language");

        $filterRepo = new FilterRepository();
        $currencies = $filterRepo->getBrokerCurrencyList();


        $tradingInstruments = $filterRepo->getBrokerStaticFieldList($language, 'trading_instruments');
        $supportOptions = $filterRepo->getBrokerStaticFieldList($language, 'support_options');




        $companyRepo = new CompanyRepository();
        $regulatorRepo = new RegulatorRepository();
        $optionsValuesRepo = new OptionValueRepository();
        $officesList = $companyRepo->getUniqueList($language, CompanyUniqueListInterface::OFFICES);

        $headquartersList = $companyRepo->getUniqueList($language, CompanyUniqueListInterface::HEADQUARTERS);
        $regulatorsList = $regulatorRepo->getUniqueList($language);

        $withdrawalMethods = $optionsValuesRepo->getUniqueList($language, BrokerOptionInterface::WITHDRAWAL_METHODS);

        // dd($withdrawalMethods);

        return  [
            [
                "field" => "filter_offices",
                "type" => "checkbox",
                "options" => $this->transform($officesList)
            ],
            [
                "field" => "filter_headquarters",
                "type" => "checkbox",
                "options" => $this->transform($headquartersList)
            ],
            [
                "field" => "filter_regulators",
                "type" => "checkbox",
                "options" => $this->transform($regulatorsList)
            ],
            [
                "field" => "filter_withdrawal_methods",
                "type" => "checkbox",
                "options" => $this->transform($withdrawalMethods)
            ],
            [
                "field" => "filter_min_deposit",
                "type" => "radio",
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
                "type" => "checkbox",
                "options" => [
                    [
                        "name" => "Islamic Accounts",
                        "value" => "islamic_accounts"
                    ],
                    [
                        "name" => "One Click Trading",
                        "value" => "1_click_trading"
                    ],
                    [
                        "name" => "Trailling Stops",
                        "value" => "trailing_stops"
                    ],
                    [
                        "name" => "Allow Scalping",
                        "value" => "allow_scalping"
                    ],
                    [
                        "name" => "Allow Hedging",
                        "value" => "allow_hedging"
                    ],
                    [
                        "name" => "Demo accounts",
                        "value" => 'non-expiring_demo_accounts'
                    ],
                    [
                        "name" => "Trading API",
                        "value" => 'trading_api'
                    ],
                    [
                        "name" => "allow news Trading",
                        "value" => 'allow_news_trading'
                    ],
                    [
                        "name" => "Allow expert advisors",
                        "value" => 'allow_expert_advisors'
                    ],
                    [
                        "name" => "Copy trading",
                        "value" => 'copy_trading'
                    ],
                    [
                        "name" => "Segregated Accounts",
                        "value" => 'segregated_accounts'
                    ],
                    [
                        "name" => "Interest on free margin",
                        "value" => 'interest_on_free_margin'
                    ],
                    [
                        "name" => "VPS",
                        "value" => 'free_vps'
                    ]
                ]
            ],
            [
                "field" => "filter_group_spread_types",
                "type" => "checkbox",
                "options" => [[
                    "name" => "Fixed Spreads",
                    "value" => 'fixed_spreads'
                ]]
            ],
            [
                "field" => "filter_group_fund_managers_features",
                "type" => "checkbox",
                "options" => [
                    [
                    "name" => "MAM/PAMM Platforms",
                    "value" => 'mam_pamm_platforms'
                ],
                [
                    "name"=>"MAM/PAMM Leaderboards",
                    "value"=>"mam_pamm_leaderboards"
                ],
                [
                    "name"=>"Managed Accounts",
                    "value"=>"managed_accounts"
                ]
                ]
            ],

            [
                "field" => "filter_account_currency",
                "type" => "checkbox",
                "options" => $this->transform($currencies, false)
            ],
            [
                "field" => "filter_trading_instruments",
                "type" => "checkbox",
                "options" => $this->transform($tradingInstruments)
            ],
            [
                "field" => "filter_support_options",
                "type" => "checkbox",
                "options" => $this->transform($supportOptions)
            ]

        ];
    }

    public function transform(array $data, $isAssociative = true)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[] = ($isAssociative) ? (["name" => $value, "value" => $key]) : (["name" => $value, "value" => $value]);
        }


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
