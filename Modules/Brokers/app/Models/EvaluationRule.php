<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Translations\Models\Zone;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Brokers\Models\EvaluationOption;
class EvaluationRule extends Model
{
   

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['label', 'slug', 'zone_id'];

    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class,'translationable');
    }

    public function zone():BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function evaluationOptions():HasMany
    {
        return $this->hasMany(EvaluationOption::class);
    }
}
