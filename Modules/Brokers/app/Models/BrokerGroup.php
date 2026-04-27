<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BrokerGroup extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'broker_groups';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // 'is_active' => 'boolean',
    ];

    /**
     * Get the brokers for the broker group.
     */
    public function brokers(): BelongsToMany
    {
        return $this->belongsToMany(Broker::class, 'broker_group_broker');
    }
}
