<?php


namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\MatrixHeader;
use Illuminate\Database\Eloquent\Collection;
use Modules\Brokers\Models\MatrixDimension;
use Modules\Brokers\Models\MatrixValue;
use Modules\Brokers\Models\MatrixDimensionOption;
class MatrixHeaderRepository
{
    /**
     * Get column headers for a matrix
     *
     * @param string $type The type of headers to retrieve
     * @param int $matrix_id The ID of the matrix
     * @param int|null $broker_id The ID of the broker (optional)
     * @param bool $broker_id_strict Whether to strictly match the broker ID.
     * If false,get headears where broker_id is null or the broker_id is the same as the broker_id in the request
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
            //->whereNull('parent_id')
            ->whereHas('matrix', function ($query) use ($matrixNameCondition) {
                $query->where(...$matrixNameCondition);
            })
            ->get();
    }
    public function flushMatrix(int $matrixId,int $brokerId)
    {
        MatrixDimensionOption::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId])->delete();
        MatrixDimension::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId])->delete();
        MatrixHeader::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId,'type'=>'row'])->delete();
        MatrixValue::where(['matrix_id'=>$matrixId,'broker_id'=>$brokerId])->delete();
       

    }
    public function insertSelectedSubOptions(array $headearSelectedSubOptions, Collection $allHeaders,int $matrixId,int $brokerId)
    {
        $headerOptions = [];
        foreach ($headearSelectedSubOptions as $rowHeaderSlug => $subOptionSlugs) {
            $rowHeaderId = $this->getHeaderId($rowHeaderSlug, $allHeaders);
            foreach ($subOptionSlugs as $subOptionSlug) {
                $subOptionId = $this->getHeaderId($subOptionSlug, $allHeaders);
                if ($subOptionId != null) {
                    // $rowHeaderSubOptionsIds[$rowHeaderId][] = $subOptionId;
                    $headerOptions[] = [
                        'matrix_id' => $matrixId,
                        'broker_id' => $brokerId,
                        'matrix_header_id' => $rowHeaderId,
                        'sub_option_id' => $subOptionId,
                    ];
                }
            }
        }
        MatrixDimensionOption::insert($headerOptions);
    }

    public function insertDimensionOptions(array $headearsSelectedOptions, array $rowDimIds, int $matrixId, int $brokerId, Collection $allHeaders)
    {
        $dimensionOptions = [];
        foreach ($headearsSelectedOptions as $rowIndex => $optionsSlugs) {
            //$rowIndex is the index of the row in the matrix
            //$rowDimIds[$rowIndex] is the id of the row dimension
            foreach ($optionsSlugs as $optionSlug) {
                $optionId = $this->getHeaderId($optionSlug, $allHeaders);
                $dimensionOptions[] = [
                    'matrix_id' => $matrixId,
                    'broker_id' => $brokerId,
                    'matrix_dimension_id' => $rowDimIds[$rowIndex],
                    'option_id' => $optionId,
                ];
            }
        }
        MatrixDimensionOption::insert($dimensionOptions);
    }

    public function getHeaderId($headerSlug, $headers)
    {
        $header = $headers->firstWhere('slug', $headerSlug);
        return $header ? $header->id : null;
    }

}
