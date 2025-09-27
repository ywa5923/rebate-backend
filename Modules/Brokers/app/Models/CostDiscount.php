<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Brokers\Models\Challenge;
use Modules\Translations\Models\Zone;

class CostDiscount extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'broker_id',
        'zone_id',
        'challenge_id',
        'value',
        'public_value',
        'previous_value',
        'is_updated_entry',
        'is_placeholder'
    ];

    /**
     * Get the broker that owns the contest.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
