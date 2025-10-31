<?php

namespace Modules\Auth\Repositories;

use Modules\Auth\Models\UserPermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserPermissionRepository
{
    protected UserPermission $model;

    public function __construct(UserPermission $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new permission.
     */
    public function create(array $data): UserPermission
    {
        return $this->model->create($data);
    }

    /**
     * Update a permission.
     */
    public function update(int $id, array $data): UserPermission
    {
        $permission = $this->model->findOrFail($id);
        $permission->update($data);
        return $permission->fresh();
    }

    /**
     * Delete a permission.
     */
    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    /**
     * Find permission by ID.
     */
    public function find(int $id): ?UserPermission
    {
        return $this->model->with('subject')->find($id);
    }

    /**
     * Get all permissions with optional filters
     */
    public function getAll(array $filters = [], string $orderBy = 'id', string $orderDirection = 'asc')
    {
        $query = $this->model->newQuery()->with('subject');

        // Apply filters
        if (!empty($filters['subject_type'])) {
            $query->where('subject_type', 'like', '%' . $filters['subject_type'] . '%');
        }

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (!empty($filters['permission_type'])) {
            $query->where('permission_type', $filters['permission_type']);
        }

        if (isset($filters['resource_id'])) {
            $query->where('resource_id', $filters['resource_id']);
        }

        if (!empty($filters['resource_value'])) {
            $query->where('resource_value', 'like', '%' . $filters['resource_value'] . '%');
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by subject name or email
        if (!empty($filters['subject'])) {
            $searchTerm = $filters['subject'];
            $query->whereHasMorph('subject', ['Modules\Auth\Models\BrokerTeamUser', 'Modules\Auth\Models\PlatformUser'], function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply ordering
        $query->orderBy($orderBy, $orderDirection);

        return $query;
    }

    /**
     * Get permissions by team user ID
     */
    public function getByTeamUserId(int $teamUserId): Collection
    {
        return $this->model->where('subject_type', 'Modules\\Auth\\Models\\BrokerTeamUser')
                           ->where('subject_id', $teamUserId)
                           ->get();
    }

}