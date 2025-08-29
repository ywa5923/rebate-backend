<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Challenge extends Model
{
   

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

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

    public function matrix(): BelongsTo
    {
        return $this->belongsTo(Matrix::class,'matrix_id');
    }
    
}
