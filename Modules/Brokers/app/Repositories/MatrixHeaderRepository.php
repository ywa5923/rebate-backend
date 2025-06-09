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
    public function getColumnHeaders(array $typeCondition, array $matrixNameCondition, ?array $brokerIdCondition, $broker_id_strict = false): Collection
    {
      //  dd($matrixIdCondition);
        return MatrixHeader::with('formType.items.dropdown.dropdownOptions')
            ->where(function ($query) use ($brokerIdCondition, $broker_id_strict) {
                if ($broker_id_strict && !empty($brokerIdCondition)) {
                    $query->where(...$brokerIdCondition);
                } else {
                    $query->whereNull('broker_id')
                        ->orWhere(...$brokerIdCondition);
                }
            })
            ->where(...$typeCondition)
            //->where(...$matrixIdCondition)
            ->whereHas('matrix', function($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->get();
    }

    public function getRowHeaders(string $type, int $matrix_id, int|null $broker_id = null, $broker_id_strict = false): Collection
    {
        return MatrixHeader::where(function ($query) use ($broker_id, $broker_id_strict) { 
                if ($broker_id_strict && $broker_id) {
                        $query->where('broker_id', $broker_id);
                } else {
                    $query->whereNull('broker_id')
                        ->orWhere('broker_id', $broker_id);
                }
            })
            ->where('type', $type)
            ->where('matrix_id', $matrix_id)
            ->get();
    }
}
