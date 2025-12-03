<?php

namespace App\Tables;

use App\Tables\TableConfigInterface;

abstract class TableConfig implements TableConfigInterface
{
    abstract public function columns(): array;
    abstract public function filters(): array;

    public function getSortableColumns(): array
    {
        return array_filter($this->columns(), function($column) {
            return $column['sortable'] === true;
        });
    }

    public function getFiltersConstraints(): array
    {
        $filtersConstraints = [];
        foreach($this->filters() as $key => $filter) {
            //all search params are recive in server as string when call $this->input($key) in Request class
            $filtersConstraints[$key] = 'nullable|string|max:555';
            // if($filter['type'] == 'text' || $filter['type'] == 'select') {
            //     $filtersConstraints[$key] = 'nullable|string|max:255';
            // }else if($filter['type'] == 'boolean') {
            //     $filtersConstraints[$key] = 'nullable|boolean';
            // }else if($filter['type'] == 'number') {
            //     $filtersConstraints[$key] = 'nullable|integer|max:1000000';
            // }
        }
        return $filtersConstraints;
    }
}
