<?php

namespace Modules\Auth\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Models\MagicLink;

class MagicLinkRepository
{
    /**
     * @var MagicLink
     */
    protected MagicLink $model;

    /**
     * Inject model instance.
     * @param MagicLink $model
     */
    public function __construct(MagicLink $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new MagicLink record.
     * @param array $data
     * @return MagicLink
     */
    public function create(array $data): MagicLink
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * Update an existing MagicLink record using provided data.
     * Requires 'id' key in $data.
     * @param array $data
     * @return MagicLink
     */
    public function update(array $data): MagicLink
    {
        if (!array_key_exists('id', $data)) {
            throw new \InvalidArgumentException("'id' is required to update MagicLink");
        }

        /** @var MagicLink $magicLink */
        $magicLink = $this->model->newQuery()->findOrFail($data['id']);
        // Do not allow changing primary key inadvertently
        unset($data['id']);

        $magicLink->update($data);
        return $magicLink->fresh();
    }

    /**
     * Find MagicLink by ID.
     * @param int $id
     * @return MagicLink|null
     */
    public function findById(int $id): ?MagicLink
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * Find MagicLink by token.
     * @param string $token
     * @return MagicLink|null
     */
    public function findByToken(string $token): ?MagicLink
    {
        return $this->model->newQuery()->where('token', $token)->first();
    }

    /**
     * Find MagicLink by token with broker relationship.
     * @param string $token
     * @return MagicLink|null
     */
    public function findByTokenWithBroker(string $token): ?MagicLink
    {
        return $this->model->newQuery()
            ->with('broker')
            ->where('token', $token)
            ->first();
    }

    /**
     * Get all MagicLinks for a specific broker.
     * @param int $brokerId
     * @param string|null $action
     * @return Collection
     */
    public function getByBrokerId(int $brokerId, ?string $action = null): Collection
    {
        $query = $this->model->newQuery()->where('broker_id', $brokerId);

        if ($action) {
            $query->where('action', $action);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get valid (not expired and not used) MagicLinks.
     * @return Collection
     */
    public function getValid(): Collection
    {
        return $this->model->newQuery()
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->get();
    }

    /**
     * Get expired MagicLinks.
     * @return Collection
     */
    public function getExpired(): Collection
    {
        return $this->model->newQuery()
            ->where('expires_at', '<=', now())
            ->get();
    }

    /**
     * Get used MagicLinks.
     * @return Collection
     */
    public function getUsed(): Collection
    {
        return $this->model->newQuery()
            ->whereNotNull('used_at')
            ->get();
    }

    /**
     * Get valid MagicLinks for a specific broker.
     * @param int $brokerId
     * @param string|null $action
     * @return Collection
     */
    public function getValidByBrokerId(int $brokerId, ?string $action = null): Collection
    {
        $query = $this->model->newQuery()
            ->where('broker_id', $brokerId)
            ->where('expires_at', '>', now())
            ->whereNull('used_at');

        if ($action) {
            $query->where('action', $action);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Check if a token exists and is unique.
     * @param string $token
     * @return bool
     */
    public function tokenExists(string $token): bool
    {
        return $this->model->newQuery()->where('token', $token)->exists();
    }

    /**
     * Delete expired MagicLinks for a specific broker.
     * @param int $brokerId
     * @return int Number of deleted records
     */
    public function deleteExpiredByBrokerId(int $brokerId): int
    {
        return $this->model->newQuery()
            ->where('broker_id', $brokerId)
            ->where('expires_at', '<=', now())
            ->delete();
    }

    /**
     * Delete expired MagicLinks for a specific team user.
     * @param int $teamUserId
     * @return int Number of deleted records
     */
    public function deleteExpiredByTeamUserId(int $teamUserId): int
    {
        return $this->model->newQuery()
            ->where('broker_team_user_id', $teamUserId)
            ->where('expires_at', '<=', now())
            ->delete();
    }

    /**
     * Delete all expired MagicLinks.
     * @return int Number of deleted records
     */
    public function deleteAllExpired(): int
    {
        return $this->model->newQuery()
            ->where('expires_at', '<=', now())
            ->delete();
    }

    /**
     * Mark all valid MagicLinks for a broker as used.
     * @param int $brokerId
     * @return int Number of updated records
     */
    public function markAsUsedByBrokerId(int $brokerId): int
    {
        return $this->model->newQuery()
            ->where('broker_id', $brokerId)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    /**
     * Mark a specific MagicLink as used.
     * @param MagicLink $magicLink
     * @return bool
     */
    public function markAsUsed(MagicLink $magicLink): bool
    {
        return $magicLink->update(['used_at' => now()]);
    }

    /**
     * Get MagicLink statistics.
     * @return array
     */
    public function getStats(): array
    {
        $total = $this->model->newQuery()->count();
        $valid = $this->model->newQuery()
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->count();
        $expired = $this->model->newQuery()
            ->where('expires_at', '<=', now())
            ->count();
        $used = $this->model->newQuery()
            ->whereNotNull('used_at')
            ->count();

        $byAction = $this->model->newQuery()
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        return [
            'total' => $total,
            'valid' => $valid,
            'expired' => $expired,
            'used' => $used,
            'by_action' => $byAction,
        ];
    }

    /**
     * Get MagicLinks by action.
     * @param string $action
     * @return Collection
     */
    public function getByAction(string $action): Collection
    {
        return $this->model->newQuery()
            ->where('action', $action)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get MagicLinks created within a date range.
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->newQuery()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get paginated MagicLinks.
     * @param int $perPage
     * @param int $page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, int $page = 1): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('broker')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get MagicLinks with filters.
     * @param array $filters
     * @return Collection
     */
    public function getWithFilters(array $filters = []): Collection
    {
        $query = $this->model->newQuery()->with('broker');

        if (isset($filters['broker_id'])) {
            $query->where('broker_id', $filters['broker_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'valid':
                    $query->where('expires_at', '>', now())
                          ->whereNull('used_at');
                    break;
                case 'expired':
                    $query->where('expires_at', '<=', now());
                    break;
                case 'used':
                    $query->whereNotNull('used_at');
                    break;
            }
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
