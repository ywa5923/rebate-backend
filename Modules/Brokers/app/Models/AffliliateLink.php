<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AffliliateLink extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['affiliate_type', 'name', 'public_name', 'previous_name', 'url', 'public_url', 'previous_url', 'currency', 'previous_currency', 'is_updated_entry', 'is_master_link', 'account_type_id', 'broker_id', 'zone_id', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function platformUrls(): BelongsToMany
    {
        return $this->belongsToMany(
            Url::class,
            'affliliate_link_url',
            'affliliate_link_id',
            'url_id'
        )->withTimestamps();
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }
}
