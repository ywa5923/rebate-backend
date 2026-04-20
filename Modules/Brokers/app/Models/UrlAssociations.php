<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Translations\Models\Zone;

class UrlAssociations extends Model
{
    protected $table = 'url_associations';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'url_id',
        'associated_url_id',
        'association_type',
        'is_public',
        'is_updated_entry',
        'zone_id',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_updated_entry' => 'boolean',
        ];
    }

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class, 'url_id');
    }

    public function associatedUrl(): BelongsTo
    {
        return $this->belongsTo(Url::class, 'associated_url_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
