<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\EvaluationStepRepository;
use Illuminate\Database\Eloquent\Collection;
class EvaluationStepService
{
    public function __construct(
        protected EvaluationStepRepository $evaluationStepRepository
    ) {}

    public function getEvaluationSteps(int $broker_id,string $language="en",?int $zone=null): Collection
    {
        return $this->evaluationStepRepository->getEvaluationSteps($broker_id,$language,$zone);
    }
}