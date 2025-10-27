<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Brokers\Database\Factories\BrokerFactory;
use Modules\Translations\Models\Translation;
use Modules\Translations\Models\Country;
use Modules\Translations\Models\Zone;

/**
 * @OA\Schema(
 *   schema="Broker",
 *   type="object",
 *   required={"broker_type_id"},
 *   @OA\Property(property="id",type="integer", format="int64"),
 *   @OA\Property(property="registration_language",type="string",nullable=true,example="en"),
 *   @OA\Property(property="registration_zone",type="string",nullable=true,example="US"),
 *   @OA\Property(property="broker_type_id",type="integer",nullable=false,example=1),
 *   @OA\Property(property="created_at",type="string",format="date-time"),
 *   @OA\Property(property="updated_at",type="string",format="date-time")
 * )
 * Class Broker
 * @package Modules\Brokers\Models
 */
class Broker extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'brokers';


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'broker_type_id',
        'registration_language',
        'registration_zone',
        'country_id',
        'zone_id',
    ];




    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function dynamicOptionsValues(): HasMany
    {
        return $this->hasMany(OptionValue::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function regulators():BelongsToMany
    {
        return $this->belongsToMany(Regulator::class);
    }
    public function brokerType():BelongsTo
    {
        return $this->belongsTo(BrokerType::class);
    }

    public function country():BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function zone():BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
