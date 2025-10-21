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
    public function find(int $id): ?MagicLink
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
     * Check if token exists.
     * @param string $token
     * @return bool
     */
    public function tokenExists(string $token): bool
    {
        return $this->model->newQuery()->where('token', $token)->exists();
    }

    /**
     * Mark MagicLink as used.
     * @param MagicLink $magicLink
     * @return bool
     */
    public function markAsUsed(MagicLink $magicLink): bool
    {
       
            return  $magicLink->update([
                'used_at' => now()
            ]);
        
       
    }

    /**
     * Get MagicLinks by subject (polymorphic).
     * @param string $subjectType
     * @param int $subjectId
     * @param string|null $action
     * @return Collection
     */
    public function getBySubject(string $subjectType, int $subjectId, ?string $action = null): Collection
    {
        $query = $this->model->newQuery()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId);

        if ($action) {
            $query->where('action', $action);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get MagicLinks by broker ID (legacy support).
     * @param int $brokerId
     * @param string|null $action
     * @return Collection
     */
    public function getByBrokerId(int $brokerId, ?string $action = null): Collection
    {
        $query = $this->model->newQuery()->where('subject_type', 'Modules\\Brokers\\Models\\Broker')
            ->where('subject_id', $brokerId);

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
     * Delete expired MagicLinks for a specific subject.
     * @param string $subjectType
     * @param int $subjectId
     * @return int Number of deleted records
     */
    public function deleteExpiredBySubject(string $subjectType, int $subjectId): int
    {
        return $this->model->newQuery()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->where('expires_at', '<=', now())
            ->delete();
    }

    /**
     * Delete expired MagicLinks for a broker (legacy support).
     * @param int $brokerId
     * @return int Number of deleted records
     */
    public function deleteExpiredByBrokerId(int $brokerId): int
    {
        return $this->model->newQuery()
            ->where('subject_type', 'Modules\\Brokers\\Models\\Broker')
            ->where('subject_id', $brokerId)
            ->where('expires_at', '<=', now())
            ->delete();
    }

    /**
     * Delete expired MagicLinks for a team user (legacy support).
     * @param int $teamUserId
     * @return int Number of deleted records
     */
    public function deleteExpiredByTeamUserId(int $teamUserId): int
    {
        return $this->model->newQuery()
            ->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
            ->where('subject_id', $teamUserId)
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
     * Mark all MagicLinks as used for a specific subject.
     * @param string $subjectType
     * @param int $subjectId
     * @return int Number of updated records
     */
    public function markAsUsedBySubject(string $subjectType, int $subjectId): int
    {
        return $this->model->newQuery()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    /**
     * Mark all MagicLinks as used for a broker (legacy support).
     * @param int $brokerId
     * @return int Number of updated records
     */
    public function markAsUsedByBrokerId(int $brokerId): int
    {
        return $this->model->newQuery()
            ->where('subject_type', 'Modules\\Brokers\\Models\\Broker')
            ->where('subject_id', $brokerId)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    /**
     * Get MagicLinks with filters.
     * @param array $filters
     * @return Collection
     */
    public function getWithFilters(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['is_expired'])) {
            if ($filters['is_expired']) {
                $query->where('expires_at', '<=', now());
            } else {
                $query->where('expires_at', '>', now());
            }
        }

        if (isset($filters['is_used'])) {
            if ($filters['is_used']) {
                $query->whereNotNull('used_at');
            } else {
                $query->whereNull('used_at');
            }
        }

        if (isset($filters['context_broker_id'])) {
            $query->where('context_broker_id', $filters['context_broker_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
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
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get MagicLink statistics.
     * @return array
     */
    public function getStats(): array
    {
        $total = $this->model->newQuery()->count();
        $valid = $this->model->newQuery()->where('expires_at', '>', now())->whereNull('used_at')->count();
        $expired = $this->model->newQuery()->where('expires_at', '<=', now())->count();
        $used = $this->model->newQuery()->whereNotNull('used_at')->count();

        $byAction = $this->model->newQuery()
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        $bySubjectType = $this->model->newQuery()
            ->selectRaw('subject_type, COUNT(*) as count')
            ->groupBy('subject_type')
            ->pluck('count', 'subject_type')
            ->toArray();

        return [
            'total' => $total,
            'valid' => $valid,
            'expired' => $expired,
            'used' => $used,
            'by_action' => $byAction,
            'by_subject_type' => $bySubjectType,
        ];
    }
}