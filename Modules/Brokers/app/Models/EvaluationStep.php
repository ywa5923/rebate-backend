<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Brokers\Models\OptionValue;

class EvaluationStep extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['broker_id', 'approved'];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

  

    public function optionValues(): MorphMany
    {
        return $this->morphMany(OptionValue::class, 'optionable');
    }
}
