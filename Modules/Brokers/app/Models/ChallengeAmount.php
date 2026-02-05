<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeAmount extends Model
{
 

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'amount',
        'currency',
        'order',
        'challenge_category_id',
    ];

    public function challengeCategory(): BelongsTo
    {
        return $this->belongsTo(ChallengeCategory::class,'challenge_category_id');
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class,'challenge_amount_id');
    }
}
