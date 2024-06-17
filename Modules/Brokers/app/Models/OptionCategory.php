<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * @OA\Property(type="string",nullable=true)
     * @var string
     */
    public $description;

     /**
     * @OA\Property(type="string",nullable=false)
     * @var string
     */
    public $default_language;

     /**
     * @OA\Property(type="datetime",nullable=false)
     * @var datetime
     */
    public $created_at;

     /**
     * @OA\Property(type="datetime",nullable=false)
     * @var datetime
     */
    public $updated_at;


   

    public function options():HasMany
    {
        return $this->hasMany(BrokerOption::class);
    }
}
