<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Brokers\Database\Factories\BrokerFactory;
use Modules\Translations\Models\Translation;


class Broker extends Model
{
    use HasFactory;
  
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ "logo","favicon", "trading_name"];

    protected static function newFactory(): BrokerFactory
    {
        return BrokerFactory::new();
    }

    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class,'translationable');
    }

    public function options():HasMany
    {
        return $this->hasMany(OptionValue::class);
    }

 
}
