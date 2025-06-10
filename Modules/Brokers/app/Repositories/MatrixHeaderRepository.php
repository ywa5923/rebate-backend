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
    public function getColumnHeaders( array $matrixNameCondition, ?array $brokerIdCondition, $broker_id_strict = false): Collection
    {
        //dd($brokerIdCondition);
        return MatrixHeader::with('formType.items.dropdown.dropdownOptions')
            ->where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
                if ($broker_id_strict && !empty($brokerIdCondition)) {
                    $query->where(...$brokerIdCondition);
                } else {
                    $query->whereNull('broker_id');
                    if($brokerIdCondition)
                        $query->orWhere(...$brokerIdCondition);
                    
                }
            })
            ->where('type', 'column')
            //->where(...$matrixIdCondition)
            ->whereHas('matrix', function($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->get();
    }

    public function getRowHeaders( array $matrixNameCondition, ?array $brokerIdCondition, $broker_id_strict = false): Collection
    {
        return MatrixHeader::where(function ($query) use ($brokerIdCondition, $broker_id_strict) { 
                if ($broker_id_strict &&  !empty($brokerIdCondition)) {
                        $query->where(...$brokerIdCondition);
                } else {
                    $query->whereNull('broker_id');
                    if($brokerIdCondition)
                        $query->orWhere(...$brokerIdCondition);
                }
            })
            ->where('type', 'row')
            ->whereHas('matrix', function($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->get();
    }
}
