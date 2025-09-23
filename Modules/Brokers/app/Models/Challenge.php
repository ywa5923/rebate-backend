<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Brokers\Models\Url;

class Challenge extends Model
{
   

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['evaluation_cost_discount','is_placeholder','challenge_category_id','challenge_step_id','challenge_amount_id','broker_id'];

    public function challengeCategory(): BelongsTo
    {
        return $this->belongsTo(ChallengeCategory::class,'challenge_category_id');
    } 

    public function challengeStep(): BelongsTo
    {
        return $this->belongsTo(ChallengeStep::class,'challenge_step_id');
    }

    public function challengeAmount(): BelongsTo
    {
        return $this->belongsTo(ChallengeAmount::class,'challenge_amount_id');
    }

    public function challengeMatrixValues(): HasMany
    {
        return $this->hasMany(ChallengeMatrixValue::class,'challenge_id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class,'broker_id');
    }

    public function urls(): MorphMany
    {
        return $this->morphMany(Url::class, 'urlable');
    }
    
}
