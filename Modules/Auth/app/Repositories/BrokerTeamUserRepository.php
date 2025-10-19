<?php

namespace Modules\Auth\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Models\BrokerTeamUser;

class BrokerTeamUserRepository
{
    protected BrokerTeamUser $model;

    public function __construct(BrokerTeamUser $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new team user.
     */
    public function create(array $data): BrokerTeamUser
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * Update a team user.
     */
    public function update(array $data): BrokerTeamUser
    {
        if (!array_key_exists('id', $data)) {
            throw new \InvalidArgumentException("'id' is required to update BrokerTeamUser");
        }

        $user = $this->model->newQuery()->findOrFail($data['id']);
        unset($data['id']);
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Find user by ID.
     */
    public function findById(int $id): ?BrokerTeamUser
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?BrokerTeamUser
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    /**
     * Find user by email with team and broker.
     */
    public function findByEmailWithRelations(string $email): ?BrokerTeamUser
    {
        return $this->model->newQuery()
            ->with(['team.broker'])
            ->where('email', $email)
            ->first();
    }

    /**
     * Get users by team ID.
     */
    public function getByTeamId(int $teamId): Collection
    {
        return $this->model->newQuery()
            ->where('broker_team_id', $teamId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active users by team ID.
     */
    public function getActiveByTeamId(int $teamId): Collection
    {
        return $this->model->newQuery()
            ->where('broker_team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get users by broker ID (through team).
     */
    public function getByBrokerId(int $brokerId): Collection
    {
        return $this->model->newQuery()
            ->whereHas('team', function ($query) use ($brokerId) {
                $query->where('broker_id', $brokerId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get users by role.
     */
    public function getByRole(string $role): Collection
    {
        return $this->model->newQuery()
            ->where('role', $role)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get users with filters.
     */
    public function getWithFilters(array $filters = []): Collection
    {
        $query = $this->model->newQuery()->with(['team.broker']);

        if (isset($filters['broker_team_id'])) {
            $query->where('broker_team_id', $filters['broker_team_id']);
        }

        if (isset($filters['broker_id'])) {
            $query->whereHas('team', function ($q) use ($filters) {
                $q->where('broker_id', $filters['broker_id']);
            });
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Delete user by ID.
     */
    public function delete(int $id): bool
    {
        $user = $this->model->newQuery()->findOrFail($id);
        return $user->delete();
    }

    /**
     * Activate user.
     */
    public function activate(int $id): bool
    {
        $user = $this->model->newQuery()->findOrFail($id);
        return $user->update(['is_active' => true]);
    }

    /**
     * Deactivate user.
     */
    public function deactivate(int $id): bool
    {
        $user = $this->model->newQuery()->findOrFail($id);
        return $user->update(['is_active' => false]);
    }

    /**
     * Update last login.
     */
    public function updateLastLogin(int $id): bool
    {
        $user = $this->model->newQuery()->findOrFail($id);
        return $user->update(['last_login_at' => now()]);
    }

    /**
     * Get user statistics.
     */
    public function getStats(?int $brokerId = null): array
    {
        $query = $this->model->newQuery();
        
        if ($brokerId) {
            $query->whereHas('team', function ($q) use ($brokerId) {
                $q->where('broker_id', $brokerId);
            });
        }

        return [
            'total_users' => $query->count(),
            'active_users' => $query->where('is_active', true)->count(),
            'inactive_users' => $query->where('is_active', false)->count(),
            'by_role' => $query->selectRaw('role, COUNT(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray(),
        ];
    }

    /**
     * Check if email exists.
     */
    public function emailExists(string $email): bool
    {
        return $this->model->newQuery()->where('email', $email)->exists();
    }
}
