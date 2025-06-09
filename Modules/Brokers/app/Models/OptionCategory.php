<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;



/**
 * @OA\Schema(
 *   schema="OptionCategory",
 *   type="object",
 *   required={"name","default_language"},
 * )
 * Class OptionCategory
 * @package Modules\Brokers\Models
 */
class OptionCategory extends Model
{
   

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

      /**
     * @OA\Property(property="id",type="integer", format="int64")
     * @OA\Property(property="name",type="string",nullable=false)
     * @OA\Property(property="description",type="string",nullable=true)
     * @OA\Property(property="default_language",type="string",nullable=false)
     * @OA\Property(property="created_at",type="datetime",nullable=false)
     * @OA\Property(property="updated_at",type="datetime",nullable=false)
     */
   
    public function options():HasMany
    {
        return $this->hasMany(BrokerOption::class);
    }

    public function translations():MorphMany
    {
        return $this->morphMany(Translation::class,'translationable');
    }
}
