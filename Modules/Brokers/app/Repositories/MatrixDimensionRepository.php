<?php

namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\MatrixDimension;
use Illuminate\Database\Eloquent\Collection;

class MatrixDimensionRepository
{

    public function __construct(protected MatrixDimension $model)
    {
       
    }

    public function getMatrixDimensions(int $matrixId, int $brokerId): Collection
    {
        return $this->model->where('matrix_id', $matrixId)
        ->where('broker_id', $brokerId)
        ->with(['matrixHeader', 'matrixDimensionOptions', 'matrixDimensionOptions.option'])
        ->get();
    }
}