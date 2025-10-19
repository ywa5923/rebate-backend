<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\MagicLink;
use Modules\Auth\Models\BrokerTeamUser;
use Modules\Auth\Repositories\MagicLinkRepository;
use Modules\Brokers\Models\Broker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class MagicLinkService
{
    protected MagicLinkRepository $repository;

    public function __construct(MagicLinkRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate a magic link for a broker.
     */
    public function generateForBroker(Broker $broker, string $action = 'login', array $metadata = [], int $expirationHours = 24): MagicLink
    {
        // Clean up old tokens for this broker
        $this->cleanupExpiredTokens($broker->id);

        $token = $this->generateUniqueToken();
        $expiresAt = now()->addHours($expirationHours);

        return $this->repository->create([
            'token' => $token,
            'broker_id' => $broker->id,
            'broker_team_user_id' => null,
            'email' => $broker->email ?? $broker->registration_language, // Fallback if no email
            'action' => $action,
            'metadata' => $metadata,
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_type' => 'broker',
        ]);
    }

    /**
     * Generate a magic link for a team user.
     */
    public function generateForTeamUser(BrokerTeamUser $teamUser, string $action = 'login', array $metadata = [], int $expirationHours = 24): MagicLink
    {
        // Clean up old tokens for this team user
        $this->cleanupExpiredTokensForTeamUser($teamUser->id);

        $token = $this->generateUniqueToken();
        $expiresAt = now()->addHours($expirationHours);

        return $this->repository->create([
            'token' => $token,
            'broker_id' => $teamUser->team->broker_id,
            'broker_team_user_id' => $teamUser->id,
            'email' => $teamUser->email,
            'action' => $action,
            'metadata' => $metadata,
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_type' => 'team_user',
        ]);
    }

    /**
     * Validate a magic link token.
     */
    public function validateToken(string $token): ?MagicLink
    {
        $magicLink = $this->repository->findByToken($token);

        if (!$magicLink || !$magicLink->isValid()) {
            return null;
        }

        return $magicLink;
    }

    /**
     * Mark a magic link as used.
     */
    public function markAsUsed(MagicLink $magicLink): bool
    {
        return $this->repository->markAsUsed($magicLink);
    }

    /**
     * Generate a unique token.
     */
    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while ($this->repository->tokenExists($token));

        return $token;
    }

    /**
     * Clean up expired tokens for a broker.
     */
    public function cleanupExpiredTokens(int $brokerId): int
    {
        return $this->repository->deleteExpiredByBrokerId($brokerId);
    }

    /**
     * Clean up expired tokens for a team user.
     */
    public function cleanupExpiredTokensForTeamUser(int $teamUserId): int
    {
        return $this->repository->deleteExpiredByTeamUserId($teamUserId);
    }

    /**
     * Clean up all expired tokens.
     */
    public function cleanupAllExpiredTokens(): int
    {
        return $this->repository->deleteAllExpired();
    }

    /**
     * Get magic links for a broker.
     */
    public function getBrokerMagicLinks(int $brokerId, ?string $action = null): Collection
    {
        return $this->repository->getByBrokerId($brokerId, $action);
    }

    /**
     * Revoke all active magic links for a broker.
     */
    public function revokeBrokerTokens(int $brokerId): int
    {
        return $this->repository->markAsUsedByBrokerId($brokerId);
    }

    /**
     * Get magic link statistics.
     */
    public function getStats(): array
    {
        return $this->repository->getStats();
    }

    /**
     * Get magic links with filters.
     */
    public function getWithFilters(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getWithFilters($filters);
    }

    /**
     * Get paginated magic links.
     */
    public function paginate(int $perPage = 15, int $page = 1): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $page);
    }
}
