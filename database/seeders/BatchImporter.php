<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class BatchImporter
{
    protected string $tableName;

    protected array $rowMapping;

    /** @var callable(array<string, mixed>, array<int, string>): array<string, mixed>|null */
    protected $rowTransformer = null;

    public function __construct(
        public string $filePath
    ) {
    }

    public function setTableInfo(string $tableName, array $rowMapping): void
    {
        $this->tableName = $tableName;
        $this->rowMapping = $rowMapping;
    }

    /**
     * @param  callable(array<string, mixed>, array<int, string>): array<string, mixed>  $transformer
     */
    public function setRowTransformer(callable $transformer): void
    {
        $this->rowTransformer = $transformer;
    }

    public function import($skip = 0, $chunk = 1000)
    {
        $mp = $this->rowMapping;
        DB::disableQueryLog();
        $this->createLazyCollection($this->filePath)
            ->skip($skip)
            ->chunk($chunk)
            ->each(function (LazyCollection $chunk) use ($mp) {

                $records = $chunk->map(function ($row) use ($mp) {
                    $cols = array_keys($mp);
                    $r = [];
                    foreach ($cols as $col) {
                        $index = $mp[$col];
                        if (is_string($index)) {
                            $r[$col] = $index;
                        } else {
                            // Negative index: use absolute value as a literal (not a CSV column).
                            $r[$col] = $index < 0 ? abs($index) : ($row[$index - 1] ?? null);
                        }
                    }

                    if ($this->rowTransformer !== null) {
                        $r = ($this->rowTransformer)($r, $row);
                    }

                    return $r;
                })->toArray();
                // dd($records);
                DB::table($this->tableName)->insert($records);
            });
    }

    protected function createLazyCollection($fileName)
    {
        return LazyCollection::make(function () use ($fileName) {

            $handle = fopen($fileName, 'r');

            while (($cols = fgetcsv($handle, 0)) !== false) {
                // $line = implode(", ", $cols);
                // $row = explode(",", $line);

                yield $cols;
            }

            fclose($handle);
        });
    }
}
