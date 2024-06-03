<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;

class ExportBrokers extends Command
{
    use TraitCommand;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-brokers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export old brokers as csv';

    //keys are cols in new table
    protected $brokersMap = [
        "id" => "id",
        'logo' => "logo",
        'trading_name' => "name",
        "user_rating" => "rating",
        'account_currencies' => "currency",
        "broker_type_id" => "type",
        'home_url'=>'home_url'
    ];
    // "overall_rating">""
    // 

    protected $brokersTextsMap = [
        'support_options' => 'support_options',
        "account_type" => "account_types",
        "trading_instruments" => "instruments",
        'language' => '',
        'default_language' => ''
    ];


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("...exporting brokers table");
        $brokersCols = $this->formatForSelectSql(array_values($this->brokersMap), "b");
        $brokerTextsCols = $this->formatForSelectSql(array_values($this->brokersTextsMap), "t");
        $sql = "select {$brokersCols},{$brokerTextsCols} from brokers b left join broker_texts t on b.id=t.broker_id and t.language='en'";
        $results = $this->DbSelect($sql);
        $newHeaders = array_keys(array_merge($this->brokersMap, $this->brokersTextsMap));
        $csvFile=$this->getCsvSeederPath("Brokers","brokers.csv");
        $this->savetoCsv($csvFile, $results, $newHeaders, "en", "en");
        
    }
}
