<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Brokers\Models\BrokerOption;

use Illuminate\Support\Facades\DB;
use Modules\Brokers\Models\OptionValue;

class ExportDynamicOptions extends Command
{
    use TraitCommand;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-dynamic-options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    //keys are cols in new table
    protected $brokersMap = [
        "broker_id" => "id",
        'position_home' => "position_home",
        'position_list' => 'position_list',
        'position_table' => 'position_table',
        '1_click_trading' => 'oneclick_trading',
        'trailing_stops' => 'trailing_stops',
        'allow_scalping' => 'scalping',
        'allow_hedging' => 'hedging',
        'allow_news_trading' => 'news_trading',
        'allow_expert_advisors' => 'expert_advisors',
        'islamic_accounts' => 'islamic_accounts',
        'social_trading' => 'social_accounts',
        'trading_api' => 'trading_api',
        'mam_pamm_platforms' => 'management_platform',
        'mam_pamm_leaderboards' => 'management_leaderboard',
        'managed_accounts' => 'managed_accounts',
        'free_vps' => 'vps',
        'non-expiring_demo_accounts' => 'nonexpiring_demo',
        'interest_on_free_margin' => 'interest_bearing_accounts',
        'broker_to_broker_transfer' => 'b2b_transfer',
        'segregated_accounts' => 'enforced_segregated_accounts',
        'crypto_rebates' => 'crypto',
        'public' => 'public',
        'visible_in_user_portal' => 'visible_users_portal',
        'new_broker' => 'new',
        'public_live_url' => 'live_url',
        'demo_trading_account_URL' => 'demo_url',
        'accept_american_clients' => 'usa',
        'accept_japanese_clients' => 'japan',
        'accept_european_clients' => 'europe',
        'accept_canadian_clients' => 'canada',
        "trustpilot_ranking" => "trustpilot"


    ];



    protected $brokersTextsMap = [
        "badge" => "badge",
        'regulation' => 'regulation',
        "trading_fees" => "commission",
        "highlights " => "highlights",
        'notes' => 'notes',
        'promotion_title' => 'promotion_title',
        'promotion_details' => 'promotion',
        "promotion_banner" => "promotion_teaser",
        "contest_title" => "contest_title",
        "contest_details" => "contest",
        'contest_banner' => 'contest_teaser',
        'short_payment_options' => 'payment_methods',
        "jurisdictions" => "jurisdictions",
        "max_cashback" => "max_leverage",
        'new_account_notes' => 'new_account_notes',
        'old_account_notes' => 'old_account_notes',
        'partner_notes' => 'become_partner_notes',
        'account_type' => 'account_types',
        'trading_nstruments' => 'instruments',
        'fixed_spreads' => 'spreads',
        'min_deposit' => 'min_deposit',
        'max_leverage' => 'max_leverage',
        'min_trade_size' => 'min_trade',
        'max_trade_size' => 'max_trade',
        'stop_out_level' => 'stopout_level',
        'execution_model' => 'execution_options',
        'deposit_methods' => 'deposit',
        'withdrawal_methods' => 'withdrawal',
        'client_funds_bank' => 'bank',
        'description' => 'description'

    ];


    public function handle()
    {
        $this->info("...exporting dynamics options from brokers and broker_texts tables");
        $brokersCols = $this->formatForSelectSql(array_values($this->brokersMap), "b");
        $brokerTextsCols = $this->formatForSelectSql(array_values($this->brokersTextsMap), "t");
        $sql = "select {$brokersCols},{$brokerTextsCols} from brokers b left join broker_texts t on b.id=t.broker_id and t.language='en'";
        $results = $this->DbSelect($sql);

        $newHeaders = array_keys(array_merge($this->brokersMap, $this->brokersTextsMap));
        $csvFile = $this->getCsvSeederPath("Brokers", "dynamic_options_values.csv");
        $this->savetoCsv($csvFile,'w', $results, $newHeaders);

    }
    
}
