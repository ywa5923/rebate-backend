<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Translations\Models\Translation;
use Modules\Translations\Models\Zone;

/**
 * @OA\Schema(
 *   schema="AcountType",
 *   type="object",
 *   required={"name", "broker_type", "broker_id"},
 *   @OA\Property(property="id", type="integer", format="int64"),
 *   @OA\Property(property="name", type="string", example="Standard Account"),
 *   @OA\Property(property="broker_type", type="string", enum={"broker", "crypto", "prop_firm"}, example="broker"),
 *   @OA\Property(property="commission_value", type="number", format="float", example=1.5),
 *   @OA\Property(property="commission_value_p", type="number", format="float", example=1.5),
 *   @OA\Property(property="commission_unit", type="string", example="pips"),
 *   @OA\Property(property="commission_unit_p", type="string", example="pips"),
 *   @OA\Property(property="execution_model", type="string", example="STP"),
 *   @OA\Property(property="execution_model_p", type="string", example="STP"),
 *   @OA\Property(property="max_leverage", type="string", example="1:500"),
 *   @OA\Property(property="max_leverage_p", type="string", example="1:500"),
 *   @OA\Property(property="spread_type", type="string", example="Fixed"),
 *   @OA\Property(property="spread_type_p", type="string", example="Fixed"),
 *   @OA\Property(property="min_deposit_value", type="string", example="100"),
 *   @OA\Property(property="min_deposit_unit", type="string", example="USD"),
 *   @OA\Property(property="min_deposit_value_p", type="string", example="100"),
 *   @OA\Property(property="min_deposit_unit_p", type="string", example="USD"),
 *   @OA\Property(property="min_trade_size_value", type="string", example="0.01"),
 *   @OA\Property(property="min_trade_size_unit", type="string", example="lots"),
 *   @OA\Property(property="min_trade_size_value_p", type="string", example="0.01"),
 *   @OA\Property(property="min_trade_size_unit_p", type="string", example="lots"),
 *   @OA\Property(property="stopout_level_value", type="string", example="20"),
 *   @OA\Property(property="stopout_level_unit", type="string", example="%"),
 *   @OA\Property(property="stopout_level_value_p", type="string", example="20"),
 *   @OA\Property(property="stopout_level_unit_p", type="string", example="%"),
 *   @OA\Property(property="trailing_stops", type="boolean", example=true),
 *   @OA\Property(property="trailing_stops_p", type="boolean", example=true),
 *   @OA\Property(property="allow_scalping", type="boolean", example=true),
 *   @OA\Property(property="allow_scalping_p", type="boolean", example=true),
 *   @OA\Property(property="allow_hedging", type="boolean", example=true),
 *   @OA\Property(property="allow_hedging_p", type="boolean", example=true),
 *   @OA\Property(property="allow_news_trading", type="boolean", example=true),
 *   @OA\Property(property="allow_news_trading_p", type="boolean", example=true),
 *   @OA\Property(property="allow_cent_accounts", type="boolean", example=false),
 *   @OA\Property(property="allow_cent_accounts_p", type="boolean", example=false),
 *   @OA\Property(property="allow_islamic_accounts", type="boolean", example=false),
 *   @OA\Property(property="allow_islamic_accounts_p", type="boolean", example=false),
 *   @OA\Property(property="mobile_url_id", type="integer", example=1),
 *   @OA\Property(property="mobile_url_id_p", type="integer", example=1),
 *   @OA\Property(property="webplaform_url_id", type="integer", example=1),
 *   @OA\Property(property="webplaform_url_id_p", type="integer", example=1),
 *   @OA\Property(property="swap_url_id", type="integer", example=1),
 *   @OA\Property(property="swap_url_id_p", type="integer", example=1),
 *   @OA\Property(property="comission_url_id", type="integer", example=1),
 *   @OA\Property(property="comission_url_id_p", type="integer", example=1),
 *   @OA\Property(property="broker_id", type="integer", example=1),
 *   @OA\Property(property="zone_id", type="integer", example=1),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="broker", type="object"),
 *   @OA\Property(property="zone", type="object"),
 *   @OA\Property(property="urls", type="array", @OA\Items(type="object")),
 *   @OA\Property(property="translations", type="array", @OA\Items(type="object"))
 * )
 * Class AccountType
 * @package Modules\Brokers\Models
 */

class AccountType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['broker_id','name'];
        

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

   

    public function urls(): MorphMany
    {
        return $this->morphMany(Url::class, 'urlable');
    }

   
    public function mobileUrls():MorphMany
    {
        return $this->urls()->where('url_type', 'mobile');
    }



    public function webplatformUrls(): MorphMany
    {
        return $this->urls()->where('url_type','webplatform');
    }

    public function swapUrls(): MorphMany
    {
        return $this->urls()->where('url_type','swap');
    }


    public function commissionUrls(): MorphMany
    {
        return $this->urls()->where('url_type','commission');
    }
    public function optionValues(): MorphMany
    {
        return $this->morphMany(OptionValue::class, 'optionable');
    }

    protected static function booted()
    {
        static::deleting(function ($accountType) {
            // Delete all related URLs (polymorphic)
            $accountType->urls()->delete();
            // Delete all related optionValues (polymorphic)
            $accountType->optionValues()->delete();
        });
    }
   
}
