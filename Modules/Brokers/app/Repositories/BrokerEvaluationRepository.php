<?php
namespace Modules\Brokers\Repositories;

use Modules\Brokers\Models\BrokerEvaluation;

class BrokerEvaluationRepository
{
    public function __construct(protected BrokerEvaluation $model)
    {
    }
}