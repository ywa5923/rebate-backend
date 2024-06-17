<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *   schema="BrokerOption",
 *   type="object",
 *   required={"name","slug","data_type","form_type","meta_data","for_crypto","for_brokers","for_props","required","default_language"},
 * )
 * Class BrokerOption
 * @package Modules\Brokers\Models
 */
class BrokerOption extends Model
{
  
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

     /**
     * @OA\Property(type="integer", format="int64")
     * @var int
     */
    public $id;

    /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $name;

     /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $slug;

     /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $data_type;

     /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $form_type;

     /**
     * @OA\Property(type="string",nullable=true)
     * @var string
     */
    public $metadata;

    /**
     * @OA\Property(type="boolean",nullable=false)
     * @var boolean
     */
    public $for_crypto;

     /**
     * @OA\Property(type="boolean",nullable=false)
     * @var boolean
     */
    public $for_brokers;

     /**
     * @OA\Property(type="boolean",nullable=false)
     * @var boolean
     */
    public $for_props;

     /**
     * @OA\Property(type="boolean",nullable=false)
     * @var boolean
     */
    public $required;

     /**
     * @OA\Property(type="boolean",nullable=false,default=true)
     * @var boolean
     */
    public $publish;

     /**
     * @OA\Property(type="integer",nullable=true)
     * @var int
     */
    public $position;

     /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $default_language;

  
    public function category():BelongsTo
    {
        return $this->belongsTo(OptionCategory::class,"option_category_id");
    }

    public function values():HasMany
    {
        return $this->hasMany(OptionValue::class);
    }
   
}
