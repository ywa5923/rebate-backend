<?php
namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class BatchImporter
{

    protected string $tableName;
    protected array $rowMapping;
    
    public function __construct(
        public string $filePath
    ){}

    public function setTableInfo(string $tableName,array $rowMapping)
    {
      $this->tableName=$tableName;
      $this->rowMapping=$rowMapping;
    }


    public function import($skip=0,$chunk=1000)
    {
        $mp=  $this->rowMapping;
        DB::disableQueryLog();
        $this->createLazyCollection($this->filePath)
            ->skip($skip)
            ->chunk($chunk)
            ->each(function (LazyCollection $chunk) use($mp) {

                $records = $chunk->map(function ($row) use ($mp) {
                   
                   $cols=array_keys($mp);
                   $r=[];
                   foreach($cols as $col)
                   {
                     $r[$col]=$row[$mp[$col]-1];
                   }
                    // return [
                    //     "id" => $row[1],
                    //     "name" => $row[2],
                    //     "title" => $row[3]
                    // ];
                    return $r;
                })->toArray();
               // dd($records);
                DB::table($this->tableName)->insert($records);
            });
    }

    protected function createLazyCollection($fileName)
    {
       return  LazyCollection::make(function () use ($fileName) {

            $handle = fopen($fileName, "r");

            while (($cols = fgetcsv($handle, 4096)) !== FALSE) {
                // $line = implode(", ", $cols);
                // $row = explode(",", $line);

                yield $cols;
            }

            fclose($handle);
        });
    }

    
    
}