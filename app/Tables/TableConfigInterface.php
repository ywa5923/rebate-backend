<?php
namespace App\Tables;

interface TableConfigInterface
{
    public function columns(): array;
    public function filters(): array;
    //public function getFiltersConstraints(): array;
   // public function getSortableColumns(): array;
}
?>