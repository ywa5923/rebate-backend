<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Brokers\Database\Factories\BrokerFactory;
use Modules\Translations\Models\Translation;

/**
 * @OA\Schema(
 *   schema="Broker",
 *   type="object",
 *   required={"trading_name"},
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
    protected $fillable = ["logo", "favicon", "trading_name"];


    /**
     * @OA\Property(property="id",type="integer", format="int64")
     * @OA\Property(property="logo",type="string",nullable=true)
     * @OA\Property(property="favicon",type="string",nullable=true)
     * @OA\Property(property="trading_name",type="string",nullable=false)
     * @OA\Property(property="home_url",type="string",nullable=false)
     * @OA\Property(property="overall_rating",type="string",nullable=false)
     * @OA\Property(property="user_rating",type="string",nullable=false)
     * @OA\Property(property="support_options",type="string",nullable=false)
     * @OA\Property(property="account_type",type="string",nullable=false)
     * @OA\Property(property="trading_instruments",type="string",nullable=false)
     * @OA\Property(property="account_currencies",type="string",nullable=false)
     * @OA\Property(property="default_language",type="string",nullable=false)
     * @OA\Property(property="created_at",type="datetime",nullable=false)
     * @OA\Property(property="updated_at",type="datetime",nullable=false)
     * 
     */

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function dynamicOptionsValues(): HasMany
    {
        return $this->hasMany(OptionValue::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class,"broker_company");
    }

    public function regulators():BelongsToMany
    {
        return $this->belongsToMany(Regulator::class);
    }
}
