<?php

namespace Modules\Translations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Translations\Models\Country;
use Modules\Brokers\Models\Broker;
/**
 * @OA\Schema(
 *   schema="Zone",
 *   type="object",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="name", type="string", example="United States"),
 *   @OA\Property(property="zone_code", type="string", example="US"),
 *   @OA\Property(property="countries", type="string", example="US,CA"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * Class Zone
 * @package Modules\Translations\Models
 */

class Zone extends Model
{
    use HasFactory;

   
    protected $fillable = [];

    public function countries():HasMany
    {
        return $this->hasMany(Country::class);
    }
    public function brokers():HasMany
    {
        return $this->hasMany(Broker::class);
    }
}
