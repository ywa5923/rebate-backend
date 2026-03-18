<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\EvaluationRule;
use Modules\Brokers\Models\EvaluationOption;
use Modules\Translations\Models\Zone;

class BrokerEvaluation extends Model
{
    

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['broker_id', 'evaluation_rule_id', 'evaluation_option_id', 'details', 'zone_id'];

    public function broker():BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function evaluationRule():BelongsTo
    {
        return $this->belongsTo(EvaluationRule::class);
    }

    public function evaluationOption():BelongsTo
    {
        return $this->belongsTo(EvaluationOption::class);
    }

    public function zone():BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
