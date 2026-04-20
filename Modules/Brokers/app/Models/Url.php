<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Translations\Models\Translation;
use Modules\Translations\Models\Zone;

/**
 * @OA\Schema(
 *   schema="Url",
 *   type="object",
 *
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
 */
class Url extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'urlable_type', 'urlable_id', 'url_type', 'url', 'public_url', 'previous_url', 'name', 'public_name', 'previous_name', 'is_updated_entry', 'is_placeholder', 'name', 'slug', 'description', 'category_position', 'option_category_id', 'broker_id', 'zone_id', 'metadata'];

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

    /**
     * Pivot rows on url_associations where this url is the source (url_id).
     */
    public function urlAssociations(): HasMany
    {
        return $this->hasMany(UrlAssociations::class, 'url_id');
    }

    /**
     * Other urls linked from this row (url_associations.url_id -> associated_url_id).
     */
    public function associatedUrls(): BelongsToMany
    {
        return $this->belongsToMany(
            Url::class,
            'url_associations',
            'url_id',
            'associated_url_id'
        )->withPivot([
            'association_type',
            'is_public',
            'is_updated_entry',
            'zone_id',
        ])->withTimestamps();
    }
}
