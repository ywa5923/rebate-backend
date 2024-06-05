<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use App\Console\Commands\TraitCommand;

class ExportCompanies extends Command
{
    use TraitCommand;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $brokersMap = [
        "company_id" => "",
        "broker_id" => "id",
        'name' => "company",
        'licence_number' => "license",
        "year_founded" => "year",
        "employees" => "employees"
    ];


    protected $brokersTextsMap = [
        "headquartes" => 'headquarters',
        'description' => "description",
        "offices" => "offices"
    ];
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("...exporting companies table");
        $brokersCols = $this->formatForSelectSql(array_values($this->brokersMap), "b");
        $brokerTextsCols = $this->formatForSelectSql(array_values($this->brokersTextsMap), "t");

        $sql0 = "set @rownum := 0";
        DB::select($sql0);
        $sql = "select @rownum := @rownum + 1 as row_number, {$brokersCols},{$brokerTextsCols} from brokers b left join broker_texts t on b.id=t.broker_id and t.language='en' where b.company !=''";
        $results = DB::select($sql);
        $newHeaders = array_keys(array_merge($this->brokersMap, $this->brokersTextsMap));
        $csvFile=$this->getCsvSeederPath("Brokers", "companies.csv");

        $this->savetoCsv($csvFile,'w', $results, $newHeaders);
       
    }
}
