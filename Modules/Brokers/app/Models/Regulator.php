<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;
use Modules\Translations\Models\Zone;

class Regulator extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'acronym',
        'country',
        'country_code',
        'zone',
        'tier_classification',
        'rating',
        'investor_protection_scheme',
        'compensation_scheme',
        'retail_leverage_restrictions',
        'website',
        'year_established',
        'jurisdiction_type',
        'notes',
        'description',
        'status',
        'status_reason',
        'is_invariant',
        'zone_id',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'year_established' => 'integer',
            'is_invariant' => 'boolean',
        ];
    }
}
