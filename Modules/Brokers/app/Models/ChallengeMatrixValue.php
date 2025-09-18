<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;


class ChallengeMatrixValue extends Model
{
   
    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = ['previous_value','value', 'public_value','is_updated_entry', 'is_invariant', 'zone_id', 'challenge_id', 'row_id', 'column_id', 'broker_id'];
   
    public function row():BelongsTo 
    {
     return $this->belongsTo(MatrixHeader::class,'row_id');
    }

    public function column():BelongsTo 
    {
     return $this->belongsTo(MatrixHeader::class,'column_id');
    }
 
    public function broker():BelongsTo
    {
     return $this->belongsTo(Broker::class,'broker_id');
    }

    public function challenge():BelongsTo
    {
     return $this->belongsTo(Challenge::class,'challenge_id');
    }
 
    public function translations():MorphMany
    {
     return $this->morphMany(Translation::class,'translationable');
    }
}
