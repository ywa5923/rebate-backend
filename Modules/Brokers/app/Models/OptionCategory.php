<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class OptionCategory extends Model
{
   

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

   

    public function options():HasMany
    {
        return $this->hasMany(BrokerOption::class);
    }
}
