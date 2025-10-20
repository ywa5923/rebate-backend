<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Brokers\Models\Broker;

class MagicLink extends Model
{
    protected $fillable = [
        'token',
        'subject_type',
        'subject_id',
        'context_broker_id',
        'email',
        'action',
        'metadata',
        'expires_at',
        'used_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the broker that owns the magic link.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    /**
     * Get the team user that owns the magic link.
     */
    public function teamUser(): BelongsTo
    {
        return $this->belongsTo(BrokerTeamUser::class, 'broker_team_user_id');
    }

    /**
     * Get the user (broker or team user) that owns the magic link.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo(null, 'subject_type', 'subject_id');
    }

    /**
     * Check if the magic link is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the magic link has been used.
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Check if the magic link is valid (not expired and not used).
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }

    /**
     * Mark the magic link as used.
     */
    public function markAsUsed(): bool
    {
        return $this->update(['used_at' => now()]);
    }

    /**
     * Scope to get only valid magic links.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
                    ->whereNull('used_at');
    }

    /**
     * Scope to get expired magic links.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to get used magic links.
     */
    public function scopeUsed($query)
    {
        return $query->whereNotNull('used_at');
    }
}
