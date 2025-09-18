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

/**
 * @OA\Schema(
 *   schema="OptionValue",
 *   type="object",
 *   required={"option_slug", "value", "broker_id", "broker_option_id"},
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="option_slug", type="string", example="minimum_deposit"),
 *   @OA\Property(property="value", type="string", example="100"),
 *   @OA\Property(property="public_value", type="string", example="$100", nullable=true),
 *   @OA\Property(property="status", type="boolean", example=true, default=true),
 *   @OA\Property(property="status_message", type="string", example="Active option", nullable=true),
 *   @OA\Property(property="default_loading", type="boolean", example=true, default=true),
 *   @OA\Property(property="type", type="string", example="number", nullable=true),
 *   @OA\Property(property="metadata", type="object", nullable=true),
 *   @OA\Property(property="is_invariant", type="boolean", example=true, default=true),
 *   @OA\Property(property="delete_by_system", type="boolean", example=false, default=false),
 *   @OA\Property(property="broker_id", type="integer", example=1),
 *   @OA\Property(property="broker_option_id", type="integer", example=1),
 *   @OA\Property(property="zone_id", type="integer", example=1, nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="optionable_id", type="integer", example=1, nullable=true),
 *   @OA\Property(property="optionable_type", type="string", example="App\Models\Broker", nullable=true)
 * )
 */
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
