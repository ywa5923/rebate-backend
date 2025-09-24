<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Brokers\Database\Factories\BrokerUrlFactory;
use Modules\Translations\Models\Zone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @OA\Schema(
 *   schema="Url",
 *   type="object",
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="urlable_type", type="string", example="Modules\\Brokers\\Models\\AcountType"),
 *   @OA\Property(property="urlable_id", type="integer", example=1),
 *   @OA\Property(property="url_type", type="string", example="website"),
 *   @OA\Property(property="url", type="string", example="https://example.com"),
 *   @OA\Property(property="url_p", type="string", example="https://example.com"),
 *   @OA\Property(property="name", type="string", example="Website"),
 *   @OA\Property(property="name_p", type="string", example="Website"),
 *   @OA\Property(property="slug", type="string", example="website"),
 *   @OA\Property(property="description", type="string", example="Company website"),
 *   @OA\Property(property="category_position", type="integer", example=1),
 *   @OA\Property(property="option_category_id", type="integer", example=1),
 *   @OA\Property(property="broker_id", type="integer", example=1),
 *   @OA\Property(property="zone_id", type="integer", example=1),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="zone", type="object"),
 *   @OA\Property(property="broker", type="object"),
 *   @OA\Property(property="translations", type="array", @OA\Items(type="object"))
 * )
 * Class Url
 * @package Modules\Brokers\Models
 */

class Url extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','urlable_type','urlable_id','url_type','url','public_url','old_url','is_updated_entry','url_p','name','name_p','slug','description','category_position','option_category_id','broker_id','zone_id'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function urlable(): MorphTo
    {
        return $this->morphTo();
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
}
