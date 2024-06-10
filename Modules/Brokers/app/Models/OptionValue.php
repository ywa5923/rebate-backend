<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Brokers\Database\Factories\OptionValueFactory;
use Modules\Translations\Models\Translation;

class OptionValue extends Model
{
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

   
    public function option():BelongsTo
    {
        return $this->belongsTo(BrokerOption::class,"broker_option_id");
    }

    public function broker():BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class,'translationable');
    }
}
