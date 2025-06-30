<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Brokers\Database\Factories\BrokerUrlFactory;
use Modules\Translations\Models\Zone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Url extends Model
{
   

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function urlable(): MorphTo
    {
        return $this->morphTo();
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
}
