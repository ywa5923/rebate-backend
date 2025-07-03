<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Brokers\Database\Factories\RegulatorFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;

/**
 * @OA\Schema(
 *     schema="Regulator",
 *     title="Regulator",
 *     description="Regulator model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Financial Conduct Authority"),
 *     @OA\Property(property="abreviation", type="string", example="FCA"),
 *     @OA\Property(property="country", type="string", example="United Kingdom"),
 *     @OA\Property(property="country_p", type="string", example="UK"),
 *     @OA\Property(property="description", type="string", example="Financial regulator in the UK"),
 *     @OA\Property(property="description_p", type="string", example="UK financial regulator"),
 *     @OA\Property(property="rating", type="number", format="float", example=4.5),
 *     @OA\Property(property="rating_p", type="number", format="float", example=4.5),
 *     @OA\Property(property="capitalization", type="string", example="High capitalization requirements"),
 *     @OA\Property(property="capitalization_p", type="string", example="High cap requirements"),
 *     @OA\Property(property="segregated_clients_money", type="string", example="Yes"),
 *     @OA\Property(property="segregated_clients_money_p", type="string", example="Yes"),
 *     @OA\Property(property="deposit_compensation_scheme", type="string", example="FSCS protection"),
 *     @OA\Property(property="deposit_compensation_scheme_p", type="string", example="FSCS"),
 *     @OA\Property(property="negative_balance_protection", type="string", example="Yes"),
 *     @OA\Property(property="negative_balance_protection_p", type="string", example="Yes"),
 *     @OA\Property(property="rebates", type="boolean", example=true),
 *     @OA\Property(property="rebates_p", type="boolean", example=true),
 *     @OA\Property(property="enforced", type="boolean", example=true),
 *     @OA\Property(property="enforced_p", type="boolean", example=true),
 *     @OA\Property(property="max_leverage", type="string", example="1:30"),
 *     @OA\Property(property="max_leverage_p", type="string", example="1:30"),
 *     @OA\Property(property="website", type="string", example="https://www.fca.org.uk"),
 *     @OA\Property(property="website_p", type="string", example="https://www.fca.org.uk"),
 *     @OA\Property(property="status", type="string", enum={"published", "pending", "rejected"}, example="published"),
 *     @OA\Property(property="status_reason", type="string", example="Approved regulator"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class Regulator extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'abreviation',
        'country',
        'country_p',
        'description',
        'description_p',
        'rating',
        'rating_p',
        'capitalization',
        'capitalization_p',
        'segregated_clients_money',
        'segregated_clients_money_p',
        'deposit_compensation_scheme',
        'deposit_compensation_scheme_p',
        'negative_balance_protection',
        'negative_balance_protection_p',
        'rebates',
        'rebates_p',
        'enforced',
        'enforced_p',
        'max_leverage_p',
        'max_leverage',
        'website',
        'website_p',
        'status',
        'status_reason'
    ];

    public function brokers():BelongsToMany
    {
        return $this->belongsToMany(Broker::class);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

}
