<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Brokers\Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;

/**
 * @OA\Schema(
 *   schema="Company",
 *   type="object",
 *   required={"name"},
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="name", type="string", example="TechCorp Solutions", maxLength=250),
 *   @OA\Property(property="name_p", type="string", example="TechCorp Solutions P", maxLength=250),
 *   @OA\Property(property="licence_number", type="string", example="TECH-2024-001"),
 *   @OA\Property(property="licence_number_p", type="string", example="TECH-2024-001-P", maxLength=250),
 *   @OA\Property(property="banner", type="string", example="https://example.com/banners/techcorp-banner.jpg"),
 *   @OA\Property(property="banner_p", type="string", example="https://example.com/banners/techcorp-banner-p.jpg"),
 *   @OA\Property(property="description", type="string", example="Leading technology solutions provider"),
 *   @OA\Property(property="description_p", type="string", example="Leading technology solutions provider"),
 *   @OA\Property(property="year_founded", type="string", example="2018"),
 *   @OA\Property(property="year_founded_p", type="string", example="2018"),
 *   @OA\Property(property="employees", type="string", example="250-500"),
 *   @OA\Property(property="employees_p", type="string", example="250-500"),
 *   @OA\Property(property="headquarters", type="string", example="San Francisco, California, USA", maxLength=1000),
 *   @OA\Property(property="headquarters_p", type="string", example="San Francisco, California, USA", maxLength=1000),
 *   @OA\Property(property="offices", type="string", example="New York, London, Singapore, Tokyo", maxLength=1000),
 *   @OA\Property(property="offices_p", type="string", example="New York, London, Singapore, Tokyo", maxLength=1000),
 *   @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="published"),
 *   @OA\Property(property="status_reason", type="string", example="", maxLength=1000),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="brokers", type="array", @OA\Items(type="object")),
 *   @OA\Property(property="translations", type="array", @OA\Items(type="object"))
 * )
 * Class Company
 * @package Modules\Brokers\Models
 */

class Company extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'name_p',
        'licence_number',
        'licence_number_p',
        'banner',
        'banner_p',
        'description',
        'description_p',
        'year_founded',
        'year_founded_p',
        'employees',
        'employees_p',
        'headquarters',
        'headquarters_p',
        'offices',
        'offices_p',
        'status',
        'status_reason',
    ];

    public function brokers():BelongsToMany
    {
        return $this->belongsToMany(Broker::class,"broker_company");
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

   
}
