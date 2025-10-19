<?php

namespace Modules\Auth\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Models\BrokerTeam;

class BrokerTeamRepository
{
    protected BrokerTeam $model;

    public function __construct(BrokerTeam $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new broker team.
     */
    public function create(array $data): BrokerTeam
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * Update a broker team.
     */
    public function update(array $data): BrokerTeam
    {
        if (!array_key_exists('id', $data)) {
            throw new \InvalidArgumentException("'id' is required to update BrokerTeam");
        }

        $team = $this->model->newQuery()->findOrFail($data['id']);
        unset($data['id']);
        $team->update($data);
        return $team->fresh();
    }

    /**
     * Find team by ID.
     */
    public function findById(int $id): ?BrokerTeam
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * Find team by ID with broker and users.
     */
    public function findByIdWithRelations(int $id): ?BrokerTeam
    {
        return $this->model->newQuery()
            ->with(['broker', 'users'])
            ->find($id);
    }

    /**
     * Get teams by broker ID.
     */
    public function getByBrokerId(int $brokerId): Collection
    {
        return $this->model->newQuery()
            ->where('broker_id', $brokerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active teams by broker ID.
     */
    public function getActiveByBrokerId(int $brokerId): Collection
    {
        return $this->model->newQuery()
            ->where('broker_id', $brokerId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get teams with filters.
     */
    public function getWithFilters(array $filters = []): Collection
    {
        $query = $this->model->newQuery()->with(['broker', 'users']);

        if (isset($filters['broker_id'])) {
            $query->where('broker_id', $filters['broker_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Delete team by ID.
     */
    public function delete(int $id): bool
    {
        $team = $this->model->newQuery()->findOrFail($id);
        return $team->delete();
    }

    /**
     * Activate team.
     */
    public function activate(int $id): bool
    {
        $team = $this->model->newQuery()->findOrFail($id);
        return $team->update(['is_active' => true]);
    }

    /**
     * Deactivate team.
     */
    public function deactivate(int $id): bool
    {
        $team = $this->model->newQuery()->findOrFail($id);
        return $team->update(['is_active' => false]);
    }

    /**
     * Get team statistics.
     */
    public function getStats(?int $brokerId = null): array
    {
        $query = $this->model->newQuery();
        
        if ($brokerId) {
            $query->where('broker_id', $brokerId);
        }

        return [
            'total_teams' => $query->count(),
            'active_teams' => $query->where('is_active', true)->count(),
            'inactive_teams' => $query->where('is_active', false)->count(),
        ];
    }
}
