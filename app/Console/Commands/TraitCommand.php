<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;


trait TraitCommand
{

    function savetoCsv($fileName,$fileOpenMode, $rows, $headers, ...$defaultCols)
    {
       
        $fp = fopen($fileName, $fileOpenMode);
        fputcsv($fp, $headers);
        foreach ($rows as $row) {
            $rowArray = array_values((array)$row);
            fputcsv($fp, array_merge($rowArray, $defaultCols));
        }
        fclose($fp);
    }

    function formatForSelectSql($cols, $tableAlias = null)
    {
        $sql = "";
        foreach ($cols as $col) {
            if (empty($col))
                continue;
            // $sql.=($tableAlias)?("'{$tableAlias}.".$col."',"):("'".$col."',");
            $sql .= ($tableAlias) ? ("{$tableAlias}." . $col . ",") : ($col . ",");
        }

        return rtrim($sql, ",");
    }

    function DbSelect($sql)
    {
        DB::statement("use cpanel");
        return DB::select($sql);
    }

    function getCsvSeederPath($module,$csvFile)
    {
        $brokersModulePath=Module::find($module)->getPath();
        $csvPath=$brokersModulePath.DIRECTORY_SEPARATOR."database".DIRECTORY_SEPARATOR."seeders".DIRECTORY_SEPARATOR."csv";
        return $csvPath.DIRECTORY_SEPARATOR.$csvFile;
    }
}
