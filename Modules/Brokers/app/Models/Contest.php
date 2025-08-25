<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Brokers\Models\OptionValue;

class Contest extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'broker_id',
    ];

    /**
     * Get the broker that owns the contest.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    /**
     * Get the option values for the contest.
     */
    public function optionValues(): MorphMany
    {
        return $this->morphMany(OptionValue::class, 'optionable');
    }
}
