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
    protected $fillable = [
        'name',
        'slug',
        'applicable_for',
        'data_type',
        'form_type',
        'meta_data',
        'for_crypto',
        'for_brokers',
        'for_props',
        'required',
        'placeholder',
        'tooltip',
        'min_constraint',
        'max_constraint',
        'load_in_dropdown',
        'default_loading',
        'default_loading_position',
        'dropdown_position',
        'category_position',
        'publish',
        'position',
        'allow_sorting',
        'default_language',
        'option_category_id',
        'dropdown_category_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'meta_data' => 'array',
    ];

    /**
     * Set the meta_data attribute.
     * Automatically decodes JSON strings to arrays, letting the array cast handle encoding.
     */
    public function setMetaDataAttribute($value)
    {
        // If it's a JSON string, decode it to array
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = null;
            }
        }
        
        // If it's already an array, encode it to JSON string for storage
        // This bypasses the array cast which might cause issues
        if (is_array($value)) {
            $this->attributes['meta_data'] = json_encode($value);
        } elseif ($value === null) {
            $this->attributes['meta_data'] = null;
        } else {
            // If it's neither array nor null, convert to null
            $this->attributes['meta_data'] = null;
        }
    }

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
