<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;


class ExportUrls extends Command
{
    use TraitCommand;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $brokersMap = [
        "broker_id" => "id",
        'mobile' => "mobile"
    ];
    // "overall_rating">""
    // 

    protected $brokersTextsMap = [
        'live_trading_account' => 'links',
        "partner_account" => "links_partner"
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("...exporting urls table");
        $brokersCols = $this->formatForSelectSql(array_values($this->brokersMap), "b");
        $brokerTextsCols = $this->formatForSelectSql(array_values($this->brokersTextsMap), "t");
        $sql = "select {$brokersCols},{$brokerTextsCols} from brokers b left join broker_texts t on b.id=t.broker_id and t.language='en'";
        $results = $this->DbSelect($sql);

       $refinedResults=$this->refineResults($results);
       
        $newHeaders = ["broker_id","url_type","name","slug","url","option_category_id"];
        $csvFile=$this->getCsvSeederPath("Brokers","urls.csv");
        $this->savetoCsv($csvFile, 'w',$refinedResults, $newHeaders);
    }

    public function refineResults($results)
    {
        $refinedResults=[];
        foreach($results as $row)
        {
            //format links same as urls table's columns
            $mobileLinks=$this->formatLink($row->id,$row->mobile,"mobile",3);
            $tradingAccountLinks=$this->formatLink($row->id,$row->links,"live_trading_account",1);
            $partnerAccountLinks=$this->formatLink($row->id,$row->links_partner,"partner_account",1);

            $refinedResults=array_merge($refinedResults, $mobileLinks, $tradingAccountLinks,$partnerAccountLinks);
            
        }
        
        return $refinedResults;
    }

    public function formatLink($brokerId,$link,$linkType,$categoryId)
    {

      if(empty($link))
      return [];
        $formatedRows=[];
        $foundMatch=preg_match_all('|<a[^>]*href="(.+)"[^>]*>(.+)</[^>]*a[^>]*>|U',$link,$out,PREG_PATTERN_ORDER);
       
        if($foundMatch){
               
            foreach($out[1] as $k=>$v)
            {
               $row=[];
                $row["broker_id"]=$brokerId;
                $row["url_type"]=$linkType;
                $row["name"]=$out[2][$k];
                $row["slug"]=Str::slug($out[2][$k]);
                $row["url"]=$v;
                $row["option_category_id"]=$categoryId;
                $formatedRows[]=$row;
            }

        }elseif($foundMatch==0){
            $row=[];
            $row["broker_id"]=$brokerId;
            $row["url_type"]=$linkType;
            $row["name"]=$link;
            $row["slug"]=Str::slug($link);
            $row["url"]="none";
            $row["option_category_id"]=$categoryId;
            $formatedRows[]=$row;
        }

        return $formatedRows;

    }

    public function test()
    {
        

        $my2='<a href="https://www.xm.com/iphone">MT4 iOS (iPhone/iPad)</a> ,<a href="https://www.xm.com/iphone">MT4 iOS (iPhone/iPad)</a>';
        $res=preg_match_all('|<a[^>]*href="(.+)"[^>]*>(.+)</[^>]*a[^>]*>|U', $my2,$out, PREG_PATTERN_ORDER);

        dd( $out);
    }




}
