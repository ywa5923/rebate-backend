<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\MagicLink;
use Modules\Auth\Models\BrokerTeamUser;
use Modules\Auth\Models\PlatformUser;
use Modules\Auth\Repositories\MagicLinkRepository;
use Modules\Brokers\Models\Broker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Mail\MagicLinkMail;
use Modules\Auth\Enums\AuthUser;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MagicLinkService
{
    protected MagicLinkRepository $repository;

    public function __construct(MagicLinkRepository $repository)
    {
        $this->repository = $repository;
    }

   
    /**
     * ===> Deprecated: Use createMagicLink instead.
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
            'subject_type' => BrokerTeamUser::class,
            'subject_id' => $teamUser->id,
            'context_broker_id' => $teamUser->team->broker_id,
            'email' => $teamUser->email,
            'action' => $action,
            'metadata' => $metadata,
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * ===> Deprecated: Mail is sent in the controller instead.
     * Send a magic link to a broker team user.
     */
    public function sendMagicLinkToBrokerTeamUser(BrokerTeamUser $brokerTeamUser, string $action = 'login', int $expirationHours = 24): MagicLink
    {
        $magicLink = $this->generateForTeamUser($brokerTeamUser, $action, [], $expirationHours);
        Mail::to($brokerTeamUser->email)->send(new MagicLinkMail($magicLink));
        return $magicLink;
    }

    

    /**
     * Create a magic link for a user.
     * @param AuthUser $authUserType
     * @param Authenticatable $userObject
     * @param string $action
     * @param array $metadata
     * @param int $expirationHours
     * @return MagicLink
     * @throws \Exception
     */
    public function createMagicLink(AuthUser $authUserType, Authenticatable $userObject,string $action = 'login', array $metadata = [],int $expirationHours = 24): MagicLink
    {
        $subjectType = null;
        $contextBrokerId = null;
        if ($authUserType === AuthUser::BROKER_TEAM_USER) {
            $subjectType = BrokerTeamUser::class;
            $contextBrokerId = $userObject->team->broker_id;
        } elseif ($authUserType === AuthUser::PLATFORM_USER) {
            $subjectType = PlatformUser::class;
           
        }

        $this->cleanupExpiredTokensForTeamUser($userObject->id);

        $token = $this->generateUniqueToken();
        $expiresAt = now()->addHours($expirationHours);

        return $this->repository->create([
            'token' => $token,
            'subject_type' => $subjectType,
            'subject_id' => $userObject->id,
            'context_broker_id' => $contextBrokerId,
            'email' => $userObject->email,
            'action' => $action,
            'metadata' => $metadata,
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * ===> Deprecated: Mail is sent in the controller instead.
     */
    public function sendMagicLinkToPlatformUser(PlatformUser $platformUser, string $action = 'login', int $expirationHours = 24): MagicLink
    {
        $magicLink = $this->generateForPlatformUser($platformUser, $action, [], $expirationHours);
        Mail::to($platformUser->email)->send(new MagicLinkMail($magicLink));
        return $magicLink;
    }

    /**
     * ===> Deprecated: Use createMagicLink instead.
     * Generate a magic link for a platform user.
     */
    public function generateForPlatformUser(PlatformUser $platformUser, string $action = 'login', array $metadata = [], int $expirationHours = 24, ?int $contextBrokerId = null): MagicLink
    {
        // Clean up old tokens for this platform user
        $this->cleanupExpiredTokensForPlatformUser($platformUser->id);

        $token = $this->generateUniqueToken();
        $expiresAt = now()->addHours($expirationHours);

        return $this->repository->create([
            'token' => $token,
            'subject_type' => PlatformUser::class,
            'subject_id' => $platformUser->id,
            'context_broker_id' => $contextBrokerId,
            'email' => $platformUser->email,
            'action' => $action,
            'metadata' => $metadata,
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
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
     * Clean up expired tokens for a team user.
     */
    public function cleanupExpiredTokensForTeamUser(int $teamUserId): int
    {
        return $this->repository->deleteExpiredBySubject('Modules\\Auth\\Models\\BrokerTeamUser', $teamUserId);
    }

    /**
     * Clean up expired tokens for a platform user.
     */
    public function cleanupExpiredTokensForPlatformUser(int $platformUserId): int
    {
        return $this->repository->deleteExpiredBySubject('Modules\\Auth\\Models\\PlatformUser', $platformUserId);
    }

    /**
     * Clean up all expired tokens.
     */
    public function cleanupAllExpiredTokens(): int
    {
        return $this->repository->deleteAllExpired();
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
