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
    protected $fillable = [
        'name',
        'broker_type',
        'commission_value',
        'commission_value_p',
        'commission_unit',
        'commission_unit_p',
        'execution_model',
        'execution_model_p',
        'max_leverage',
        'max_leverage_p',
        'spread_type',
        'spread_type_p',
        'min_deposit_value',
        'min_deposit_unit',
        'min_deposit_value_p',
        'min_deposit_unit_p',
        'min_trade_size_value',
        'min_trade_size_unit',
        'min_trade_size_value_p',
        'min_trade_size_unit_p',
        'stopout_level_value',
        'stopout_level_unit',
        'stopout_level_value_p',
        'stopout_level_unit_p',
        'trailing_stops',
        'trailing_stops_p',
        'allow_scalping',
        'allow_scalping_p',
        'allow_hedging',
        'allow_hedging_p',
        'allow_news_trading',
        'allow_news_trading_p',
        'allow_cent_accounts',
        'allow_cent_accounts_p',
        'allow_islamic_accounts',
        'allow_islamic_accounts_p',
        'mobile_url_id',
        'mobile_url_id_p',
        'webplaform_url_id',
        'webplaform_url_id_p',
        'swap_url_id',
        'swap_url_id_p',
        'comission_url_id',
        'comission_url_id_p',
        'broker_id',
        'zone_id'
    ];

    protected $casts = [
        'trailing_stops' => 'boolean',
        'trailing_stops_p' => 'boolean',
        'allow_scalping' => 'boolean',
        'allow_scalping_p' => 'boolean',
        'allow_hedging' => 'boolean',
        'allow_hedging_p' => 'boolean',
        'allow_news_trading' => 'boolean',
        'allow_news_trading_p' => 'boolean',
        'allow_cent_accounts' => 'boolean',
        'allow_cent_accounts_p' => 'boolean',
        'allow_islamic_accounts' => 'boolean',
        'allow_islamic_accounts_p' => 'boolean',
        'commission_value' => 'decimal:5',
        'commission_value_p' => 'decimal:5'
    ];

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
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

   
}
