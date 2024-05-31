<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\DB;

class ExportDealTypes extends Command
{
    use TraitCommand;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-deal-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $dealtypesMap = [
        "id" => "id",
        'code' => "code"
    ];


    protected $dealtypeTextsMap = [
        "name"=>"name",
        'description' => "description",
        "example" => "example"
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("...exporting dealtypes table");
        $this->exportDealtypes();
        
        $this->info("...exporting  brokers_dealtypes table");
        $this->exportBrokersDealtypes();
       
    }

    public function exportDealtypes()
    {
        $brokersCols=$this->formatForSelectSql(array_values($this->dealtypesMap),"d");
        $brokerTextsCols=$this->formatForSelectSql(array_values($this->dealtypeTextsMap),"t");
        
         $sql="select {$brokersCols},{$brokerTextsCols} from dealtypes d left join dealtype_texts t on d.id=t.dealtype_id and t.language='en'";
        //  DB::statement("use cpanel");
        //  $results =DB::select($sql);
         $results=$this->DbSelect($sql);
         $newHeaders=array_keys(array_merge($this->dealtypesMap,$this->dealtypeTextsMap));
          $csvFile=$this->getCsvSeederPath("Brokers","dealtypes.csv");
         
         $this->savetoCsv( $csvFile,$results, $newHeaders,"en");

    }

    public function exportBrokersDealtypes()
    {
        $sql="select id,dealtype_id from brokers b left join broker_texts t on b.id=t.broker_id and t.language='en'";
        $results=$this->DbSelect($sql);
        $filteredResults=array_filter($results,function($row){
            return ($row->dealtype_id!=0)?true:false;
        });
    
        $newHeaders=["broker_id","dealtype_id"];
        $csvFile=$this->getCsvSeederPath("Brokers","brokers_dealtypes.csv");
        $this->savetoCsv( $csvFile,$filteredResults, $newHeaders);
    }
}
