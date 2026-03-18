<?php

namespace Modules\Brokers\Services;

use Modules\Brokers\Repositories\EvaluationRuleRepository;
use Modules\Brokers\Repositories\BrokerEvaluationRepository;

class EvaluationRuleService
{
    public function __construct(
        protected EvaluationRuleRepository $evaluationRuleRepository,
        protected BrokerEvaluationRepository $brokerEvaluationRepository
    ) {}

    
}
