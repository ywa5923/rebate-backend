<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;

class AccountType extends Model
{
    //these constants are used to return the account type's options value or options public values
    const RETURN_TYPE_VALUE = 'value';

    const RETURN_TYPE_PUBLIC_VALUE = 'public_value';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['broker_id'];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function urls(): MorphMany
    {
        return $this->morphMany(Url::class, 'urlable');
    }

    public function mobileUrls(): MorphMany
    {
        return $this->urls()->where('url_type', 'mobile');
    }

    public function webplatformUrls(): MorphMany
    {
        return $this->urls()->where('url_type', 'webplatform');
    }

    public function swapUrls(): MorphMany
    {
        return $this->urls()->where('url_type', 'swap');
    }

    public function commissionUrls(): MorphMany
    {
        return $this->urls()->where('url_type', 'commission');
    }

    public function optionValues(): MorphMany
    {
        return $this->morphMany(OptionValue::class, 'optionable');
    }

    public function getAllAccountTypeUrls()
    {
        $class = self::class;

        return Url::where(function ($query) use ($class) {
            $query->where(function ($q) {
                $q->where('urlable_type', self::class)
                    ->where('urlable_id', $this->id);
            })->orWhere(function ($q) use ($class) {
                $q->where('urlable_type', $class)
                    ->whereNull('urlable_id');
            });
        });
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

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    /**
     * Get account types names
     * Used in MatrixHeaderRepository line 607
     */
    public function getAccountTypesNames(int $broker_id, string $return_type = self::RETURN_TYPE_VALUE, string $language_code = 'en'): array
    {
        $with = [
            'optionValues' => function ($q) {
                $q->whereHas('option', fn ($oq) => $oq->where('option_slug', 'account_type_name'));
            },
        ];

        if ($language_code !== 'en') {
            $with['optionValues.translations'] = function ($q) use ($language_code) {
                $q->where('language_code', $language_code);
            };
        }

        $accountTypes = $this->newQuery()
            ->with($with)
            ->where('broker_id', $broker_id)
            ->get();

        return $accountTypes->map(function ($accountType) use ($return_type) {
            $name = $return_type === self::RETURN_TYPE_VALUE ? $accountType->optionValues->first()->value : $accountType->optionValues->first()->public_value;

            return [
                'id' => $accountType->id,
                'name' => $name,
                'slug' => strtolower(str_replace(' ', '-', $name)),
            ];
        })->toArray();
    }
}
