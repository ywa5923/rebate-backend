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
     * @OA\Property(type="integer", format="int64")
     * @var int
     */
    public $id;

    /**
     * @OA\Property(type="string",nullable=true)
     * @var string
     */
    public $logo;

    /**
     * @OA\Property(type="string",nullable=true)
     * @var string
     */
    public $favicon;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $trading_name;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $home_url;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $overall_rating;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $user_rating;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $support_options;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $account_type;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $trading_instruments;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $account_currencies;

  
    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $default_language;


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
}
