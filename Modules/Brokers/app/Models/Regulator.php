<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Brokers\Database\Factories\RegulatorFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;

class Regulator extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function brokers():BelongsToMany
    {
        return $this->belongsToMany(Broker::class);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

}
