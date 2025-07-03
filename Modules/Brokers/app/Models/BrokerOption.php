<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;
//use OpenApi\Annotations\Property;
use OpenApi\Attributes\Property;

/**
 * @OA\Schema(
 *   schema="BrokerOption",
 *   type="object",
 *   required={"name","slug","data_type","form_type","meta_data","for_crypto","for_brokers","for_props","required","default_language"},
 *   @OA\Property(property="id",type="integer",nullable=false),
 *   @OA\Property(property="name",type="string",nullable=false),
 *   @OA\Property(property="slug",type="string",nullable=false),
 *   @OA\Property(property="default_language",type="string",nullable=false),
 *   @OA\Property(property="data_type",type="string",nullable=false),
 *   @OA\Property(property="form_type",type="string",nullable=false),
 *   @OA\Property(property="meta_data",type="string",nullable=true),
 *   @OA\Property(property="for_crypto",type="boolean",nullable=false),
 *   @OA\Property(property="for_brokers",type="boolean",nullable=false),
 *   @OA\Property(property="for_props",type="boolean",nullable=false),
 *   @OA\Property(property="required",type="boolean",nullable=false),
 *   @OA\Property(property="publish",type="boolean",nullable=false,default=true),
 *   @OA\Property(property="position",type="integer",nullable=true),
 *   @OA\Property(property="created_at",type="datetime",nullable=false),
 *   @OA\Property(property="updated_at",type="datetime",nullable=false)
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


    public function category():BelongsTo
    {
        return $this->belongsTo(OptionCategory::class,"option_category_id");
    }

    public function values($brokerId=null):HasMany
    {
        $query= $this->hasMany(OptionValue::class);
        if($brokerId)
        {
            $query->where("broker_id",$brokerId);
        }
        return $query;
    }

    public function dropdownCategory():BelongsTo
    {
        return $this->belongsTo(DropdownCategory::class,"dropdown_category_id");
    }

    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class,'translationable');
    }
   
}
