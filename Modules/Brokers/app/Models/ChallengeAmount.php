<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeAmount extends Model
{
 

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function challengeCategory(): BelongsTo
    {
        return $this->belongsTo(ChallengeCategory::class,'challenge_category_id');
    }

    
}
