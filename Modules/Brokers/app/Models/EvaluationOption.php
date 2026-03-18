<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Brokers\Models\EvaluationRule;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;
class EvaluationOption extends Model
{
    

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['evaluation_rule_id', 'option_label', 'option_value', 'description', 'is_getter'];

    public function evaluationRule():BelongsTo
    {
        return $this->belongsTo(EvaluationRule::class);
    }

    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class,'translationable');
    }
}
