<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\TraitCommand;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;

class ExportRegulators extends Command
{
    use TraitCommand;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-regulators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $regulatorsMap = [
        "id" => "id",
        'abreviation' => "acronym",
        'rating' => "rating",
        'capitalization' => "capitalization",
        'enforced' => "enforced",
        'website' => "url",

    ];

    // dynamic options "segregated_clients_money", "deposit_compensation_scheme", "negative_balance_protection","rebates","max_leverage"  

    protected $regulatorsTextsMap = [
        'name' => "name",
        "country" => "country",
        "description" => "description",
    ];



    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("...exporting regulators table");
        $this->exportRegulators();
       
        $this->info("...exporting brokers_regulators table");
        $this->exportBrokersRegulators();
      


    }

    public function exportRegulators()
    {
        $regulatorsCols = $this->formatForSelectSql(array_values($this->regulatorsMap), "r");
        $regulatorsTextsCols = $this->formatForSelectSql(array_values($this->regulatorsTextsMap), "t");

        $sql = "select {$regulatorsCols},{$regulatorsTextsCols} from regulators r left join regulator_texts t on r.id=t.regulator_id and t.language='en' ";
        $results=$this->DbSelect($sql);
        $newHeaders = array_keys(array_merge($this->regulatorsMap, $this->regulatorsTextsMap));
        $csvFile = $this->getCsvSeederPath("Brokers", "regulators.csv");
        $this->savetoCsv($csvFile,'w', $results, $newHeaders);

        $sqlRo = "select r.id,{$regulatorsTextsCols} from regulators r left join regulator_texts t on r.id=t.regulator_id and t.language='ro' ";
        $resultsRo = $this->DbSelect($sqlRo);
        $newHeadersRo = array_keys($this->regulatorsTextsMap);
        array_unshift($newHeadersRo,'id');
        $csvFileRo = $this->getCsvSeederPath("Brokers", "regulators_ro.csv");
        $this->savetoCsv($csvFileRo,'w',  $resultsRo, $newHeadersRo);
    }

    public function exportBrokersRegulators()
    {
        $sql = "select id,regulator1_id,regulator2_id,regulator3_id,regulator4_id,regulator5_id from brokers";
        $results = DB::select($sql);
        $results = $this->filterResults($results);
        $newHeaders = ["broker_id", "regulator_id"];
        $csvFile = $this->getCsvSeederPath("Brokers", "brokers_regulators.csv");
        $this->savetoCsv($csvFile,'w', $results, $newHeaders);
    }
    public function filterResults($rows)
    {
        $results = [];
        foreach ($rows as $row) {
            $rowArray = (array)$row;
            $id = array_shift($rowArray);

            foreach ($rowArray as $col) {
                //regulators with id 0 and 2 don't exist
                if ($col == 0 || $col==2)
                    continue;

                $results[] = [$id, $col];
            }
        }

        return $results;
    }
}
