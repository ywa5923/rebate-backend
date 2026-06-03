<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Brokers\Database\Factories\OptionValueFactory;
use Modules\Translations\Models\Translation;


class OptionValue extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'optionable_id',
        'optionable_type',
        'option_slug',
        'previous_value',
        'is_updated_entry',
        'value',
        'public_value',
        'status',
        'status_message',
        'default_loading',
        'type',
        'metadata',
        'is_invariant',
        'delete_by_system',
        'broker_id',
        'broker_option_id',
        'zone_id'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'boolean',
        'default_loading' => 'boolean',
        'is_invariant' => 'boolean',
        'delete_by_system' => 'boolean',
        'is_updated_entry' => 'boolean',
        'metadata' => 'array',
    ];
   
    public function option():BelongsTo
    {
        return $this->belongsTo(BrokerOption::class,"broker_option_id");
    }

    public function broker():BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function zone():BelongsTo
    {
        return $this->belongsTo(\Modules\Translations\Models\Zone::class);
    }

    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class,'translationable');
    }

    public function optionable():MorphTo
    {
        return $this->morphTo();
    }
}
