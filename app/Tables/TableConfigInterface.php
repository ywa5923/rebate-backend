<?php
namespace App\Tables;

interface TableConfigInterface
{
    public function columns(): array;
    public function filters(): array;
}
?>