<?php

namespace Modules\Brokers\Services;

use App\Services\BaseQueryParser;
use Illuminate\Http\Request;

class BrokersQueryParser extends BaseQueryParser
{
    protected $querySafeParams = [
       "language"=>['eq'],
       "country"=>['eq'],
       "order_by"=>['eq'],
       "order_direction"=>['eq'],
       "columns"=>['in'],
       "filter_offices"=>['in'],
       "filter_headquarters"=>['in'],
       "filter_min_deposit"=>['lt'],
       "filter_withdrawal_methods"=>['in'],
       "filter_group_trading_account_info"=>["in"],
       "filter_group_spread_types"=>["in"],
       "filter_group_fund_managers_features"=>["in"],
       "filter_account_currency"=>["in"],
       "filter_support_options"=>["in"],
       "filter_trading_instruments"=>["in"],
       "filter_regulators"=>["in"],
       "filter_mobile"=>["in"],
       "filter_web"=>["in"]

    ];
  
    protected $columnMap = [
       "language"=>"language_code",
       "filter_offices"=>"offices",
       "filter_headquarters"=>"headquarters",
       "filter_min_deposit"=>"min_deposit",
       "filter_withdrawal_methods"=>"withdrawal_methods",
       "filter_account_currency"=>"account_currencies",
       "filter_support_options"=>"support_options",
       "filter_trading_instruments"=>"trading_instruments",
       "filter_regulators"=>"abreviation",
       "filter_mobile"=>"mobile_platform_link",
       "filter_web"=>"web_platform_link",
      
    ];

    public function parse(Request $request)
    {
      return parent::parse($request);
      
    }
    

   
}

