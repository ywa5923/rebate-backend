<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\MorphMany;




class Company extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'broker_id',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

  

    public function optionValues(): MorphMany
    {
        return $this->morphMany(OptionValue::class, 'optionable');
    }

   
}
