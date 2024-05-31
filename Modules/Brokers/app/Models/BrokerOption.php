<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrokerOption extends Model
{
   
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];


    public function category():BelongsTo
    {
        return $this->belongsTo(OptionCategory::class,"option_category_id");
    }

    public function values():HasMany
    {
        return $this->hasMany(OptionValue::class);
    }
   
}
