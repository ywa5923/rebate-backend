<?php

namespace Modules\Translations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Translations\Models\Zone;
use Modules\Brokers\Models\Broker;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function zone():BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
    public function brokers():HasMany
    {
        return $this->hasMany(Broker::class);
    }
}
