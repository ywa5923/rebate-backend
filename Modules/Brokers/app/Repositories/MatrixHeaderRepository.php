<?php


namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\MatrixHeader;
use Illuminate\Database\Eloquent\Collection;

class MatrixHeaderRepository
{
    /**
     * Get column headers for a matrix
     *
     * @param string $type The type of headers to retrieve
     * @param int $matrix_id The ID of the matrix
     * @param int|null $broker_id The ID of the broker (optional)
     * @param bool $broker_id_strict Whether to strictly match the broker ID
     * @return Collection
     */
    public function getColumnHeaders(array $matrixNameCondition, ?array $brokerIdCondition, $broker_id_strict = false): Collection
    {
        //$matrixNameCondition is an array of 3 elements:
        // array:3 [ 
        //     0 => "name"
        //     1 => "="
        //     2 => "Matrix-1"
        //   ]
        // $brokerIdCondition is an array of 3 elements:
        // array:3 [ 
        //     0 => "broker_id"
        //     1 => "="
        //     2 => "1"
        //   ]
        // $broker_id_strict is a boolean value

        return MatrixHeader::with('formType.items.dropdown.dropdownOptions')
            ->where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
                if ($broker_id_strict && !empty($brokerIdCondition)) {
                    $query->where(...$brokerIdCondition);
                } else {
                    $query->whereNull('broker_id');
                    if ($brokerIdCondition)
                        $query->orWhere(...$brokerIdCondition);
                }
            })
            ->where('type', 'column')
            //->where(...$matrixIdCondition)
            ->whereHas('matrix', function ($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->get();
    }

    public function getRowHeaders(array $matrixNameCondition, ?array $brokerIdCondition, $broker_id_strict = false): Collection
    {
        return MatrixHeader::where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
            if ($broker_id_strict &&  !empty($brokerIdCondition)) {
                $query->where(...$brokerIdCondition);
            } else {
                $query->whereNull('broker_id');
                if ($brokerIdCondition)
                    $query->orWhere(...$brokerIdCondition);
            }
        })
            ->where('type', 'row')
            ->whereNull('parent_id')
            ->whereHas('matrix', function ($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->with('children')
            ->get();
    }

    public function getAllHeaders(array $matrixNameCondition, ?array $brokerIdCondition, $broker_id_strict = false): Collection
    {
        return MatrixHeader::where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
            if ($broker_id_strict &&  !empty($brokerIdCondition)) {
                $query->where(...$brokerIdCondition);
            } else {
                $query->whereNull('broker_id');
                if ($brokerIdCondition)
                    $query->orWhere(...$brokerIdCondition);
            }
        })
            ->whereNull('parent_id')
            ->whereHas('matrix', function ($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->get();
    }
}
